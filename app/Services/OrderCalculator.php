<?php

namespace App\Services;

use App\Models\MenuItem;
use Carbon\Carbon;
use Illuminate\Support\Collection;

class OrderCalculator
{
    public function __construct(private OrderDraft $drafts) {}

    /**
     * @param  array<int, int>  $items
     * @return array{lines: list<array<string, mixed>>, total: int}
     */
    public function perDeliveryBasket(array $items, int $serving): array
    {
        $menu = $this->menuFor(array_keys($items));
        $lines = [];
        $total = 0;

        foreach ($items as $id => $quantity) {
            $quantity = (int) $quantity;
            $item = $menu->get((int) $id);
            if ($quantity < 1 || ! $item) {
                continue;
            }

            $lineTotal = (int) $item->price_per_person * $serving * $quantity;
            $total += $lineTotal;
            $lines[] = [
                'id' => $item->id,
                'name' => $item->name,
                'price' => (int) $item->price_per_person,
                'quantity' => $quantity,
                'image' => $item->image,
                'line_total' => $lineTotal,
            ];
        }

        return ['lines' => $lines, 'total' => $total];
    }

    /**
     * @return array<string, mixed>
     */
    public function quote(array $draft): array
    {
        $draft = $this->drafts->constrain($draft);
        $errors = [];
        $serving = (int) $draft['serving_size'];
        $packing = (string) ($draft['packing'] ?? 'separate');
        $mode = (string) ($draft['per_person_schedule'] ?? 'same');
        $min = $this->drafts->minDate();
        $allowed = config('noshmark.serving_sizes');

        if ($packing !== 'pateela' && ! in_array($serving, $allowed, true)) {
            $errors[] = 'Choose a serving size of 2, 4, 8, or 12 persons.';
        }

        if ($packing === 'pateela' && $serving !== (int) config('noshmark.pateela_persons')) {
            $errors[] = 'Whole pateela is available for 30 persons.';
        }

        $deliveries = $draft['deliveries'] ?? [];
        if (count($deliveries) > (int) config('noshmark.max_deliveries')) {
            $errors[] = 'An order can include up to '.config('noshmark.max_deliveries').' delivery days.';
        }

        $ids = [];
        foreach ($deliveries as $lines) {
            foreach ($lines as $line) {
                $ids[] = (int) ($line['menu_item_id'] ?? 0);
            }
        }

        $menu = $this->menuFor($ids);
        $days = [];

        foreach ($deliveries as $date => $lines) {
            try {
                $day = Carbon::parse($date)->startOfDay();
            } catch (\Throwable) {
                $errors[] = 'One of the delivery dates is not valid.';

                continue;
            }

            $label = $day->format('D, j M');
            if ($day->lt($min)) {
                $errors[] = 'Orders must be placed at least '.config('noshmark.lead_days')." days before delivery ({$label}).";
            }

            $priced = $this->priceLines($mode, $serving, is_array($lines) ? $lines : [], $menu);
            foreach ($priced['errors'] as $error) {
                $errors[] = $label.': '.$error;
            }

            $days[] = [
                'date' => $day->toDateString(),
                'label' => $day->format('l, j M Y'),
                'menu_for' => 'Menu for '.$day->format('D, j M'),
                'weekday' => $day->format('D'),
                'day_num' => $day->format('j'),
                'month' => $day->format('M'),
                'lines' => $priced['lines'],
                'day_total' => $priced['total'],
            ];
        }

        usort($days, fn ($a, $b) => strcmp($a['date'], $b['date']));

        if ($days === []) {
            $errors[] = 'Select at least one delivery day.';
        }

        $count = count($days);
        $dishes = (int) array_sum(array_column($days, 'day_total'));
        $packingTotal = 0;

        if ($count > 0) {
            $packingTotal = $packing === 'pateela'
                ? (int) config('noshmark.pateela_price') * $count
                : (int) config('noshmark.separate_packing_per_person') * $serving * $count;
        }

        $deliveryTotal = (int) config('noshmark.delivery_fee') * $count;

        return [
            'errors' => array_values(array_unique($errors)),
            'serving_size' => $serving,
            'packing' => $packing,
            'packing_label' => (string) config('noshmark.packing.'.$packing.'.label', $packing),
            'schedule' => (string) ($draft['schedule'] ?? 'weekly'),
            'schedule_label' => (string) config('noshmark.schedules.'.($draft['schedule'] ?? '').'.label', 'Weekly'),
            'schedule_summary' => (string) config('noshmark.schedules.'.($draft['schedule'] ?? '').'.summary', ''),
            'per_person_schedule' => $mode,
            'per_person_label' => (string) config('noshmark.per_person_schedules.'.$mode, $mode),
            'days_per_week' => (int) ($draft['days_per_week'] ?? 1),
            'menus_vary' => (bool) ($draft['menus_vary'] ?? true),
            'days' => $days,
            'delivery_count' => $count,
            'dishes_total' => $dishes,
            'packing_total' => $packingTotal,
            'delivery_total' => $deliveryTotal,
            'grand_total' => $dishes + $packingTotal + $deliveryTotal,
        ];
    }

    /**
     * @param  list<array<string, mixed>>  $rawLines
     * @return array{lines: list<array<string, mixed>>, errors: list<string>, total: int}
     */
    public function priceLines(string $mode, int $serving, array $rawLines, Collection $menu): array
    {
        $built = [];
        $errors = [];
        $personsUsed = 0;
        $maxQty = (int) config('noshmark.max_quantity');

        foreach ($rawLines as $line) {
            if ($mode === 'personalized' && (int) ($line['persons'] ?? 0) < 1) {
                continue;
            }

            $item = $menu->get((int) ($line['menu_item_id'] ?? 0));
            if (! $item) {
                $errors[] = 'Choose a menu item.';

                continue;
            }

            if ($mode === 'personalized') {
                $persons = (int) $line['persons'];
                $personsUsed += $persons;
                $built[] = $this->line($item, 1, $persons, (int) $item->price_per_person * $persons);
            } else {
                $quantity = max(1, min($maxQty, (int) ($line['quantity'] ?? 1)));
                $built[] = $this->line($item, $quantity, null, (int) $item->price_per_person * $serving * $quantity);
            }
        }

        if ($mode === 'personalized' && $personsUsed !== $serving) {
            $errors[] = "Portions must add up to {$serving} persons.";
        }

        if ($built === []) {
            $errors[] = 'Choose a menu item.';
        }

        return [
            'lines' => $built,
            'errors' => $errors,
            'total' => (int) array_sum(array_column($built, 'line_total')),
        ];
    }

    /**
     * @param  list<int|string>  $ids
     */
    public function menuFor(array $ids): Collection
    {
        $ids = array_values(array_unique(array_filter(array_map('intval', $ids))));

        if ($ids === []) {
            return collect();
        }

        return MenuItem::query()->whereIn('id', $ids)->where('is_active', true)->get()->keyBy('id');
    }

    /**
     * @return array<string, mixed>
     */
    private function line(MenuItem $item, int $quantity, ?int $persons, int $lineTotal): array
    {
        return [
            'menu_item_id' => $item->id,
            'name' => $item->name,
            'description' => $item->description,
            'image' => $item->image,
            'unit_price' => (int) $item->price_per_person,
            'quantity' => $quantity,
            'persons' => $persons,
            'line_total' => $lineTotal,
        ];
    }
}
