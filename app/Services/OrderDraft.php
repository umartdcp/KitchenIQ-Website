<?php

namespace App\Services;

use Carbon\Carbon;

class OrderDraft
{
    public const SESSION_KEY = 'noshmark.draft';

    public function defaults(): array
    {
        return [
            'serving_size' => 4,
            'previous_serving_size' => 4,
            'items' => [],
            'schedule' => 'weekly',
            'start_date' => $this->minDate()->toDateString(),
            'menus_vary' => true,
            'packing' => 'separate',
            'days_per_week' => 3,
            'per_person_schedule' => 'same',
            'address' => '',
            'deliveries' => [],
        ];
    }

    public function get(): array
    {
        return array_merge($this->defaults(), session(self::SESSION_KEY, []));
    }

    public function put(array $draft): void
    {
        session([self::SESSION_KEY => $draft]);
    }

    public function forget(): void
    {
        session()->forget(self::SESSION_KEY);
    }

    public function minDate(): Carbon
    {
        return now()->startOfDay()->addDays((int) config('noshmark.lead_days'));
    }

    /**
     * @return list<string>
     */
    public function suggestedDates(string $start, int $count): array
    {
        $count = max(1, min(7, $count));
        $cursor = Carbon::parse($start)->startOfDay();
        $min = $this->minDate();

        if ($cursor->lt($min)) {
            $cursor = $min->copy();
        }

        $gap = match (true) {
            $count >= 5 => 1,
            $count === 3 => 2,
            $count === 2 => 3,
            default => 1,
        };

        $dates = [];
        for ($i = 0; $i < $count; $i++) {
            $dates[] = $cursor->toDateString();
            $cursor->addDays($gap);
        }

        return $dates;
    }

    /**
     * @param  array<int|string, mixed>  $quantities
     * @return array<int, int>
     */
    public function normalizeItems(array $quantities): array
    {
        $max = (int) config('noshmark.max_quantity');
        $clean = [];

        foreach ($quantities as $id => $value) {
            $quantity = (int) $value;
            if ($quantity > 0) {
                $clean[(int) $id] = min($max, $quantity);
            }
        }

        return $clean;
    }

    public function basketLines(array $draft): array
    {
        $items = collect($draft['items'] ?? [])
            ->map(fn ($quantity, $id) => ['id' => (int) $id, 'qty' => (int) $quantity])
            ->filter(fn ($row) => $row['qty'] > 0)
            ->values();

        if ($items->isEmpty()) {
            return [];
        }

        if (($draft['packing'] ?? 'separate') === 'pateela') {
            return [[
                'menu_item_id' => $items->first()['id'],
                'quantity' => 1,
                'persons' => null,
            ]];
        }

        if (($draft['per_person_schedule'] ?? 'same') === 'personalized') {
            return [[
                'menu_item_id' => $items->first()['id'],
                'quantity' => 1,
                'persons' => (int) $draft['serving_size'],
            ]];
        }

        return $items->map(fn ($row) => [
            'menu_item_id' => $row['id'],
            'quantity' => $row['qty'],
            'persons' => null,
        ])->all();
    }

    public function ensureDeliveries(array $draft): array
    {
        if (! empty($draft['deliveries'])) {
            return $this->constrain($draft);
        }

        $lines = $this->basketLines($draft);
        $draft['deliveries'] = [];

        foreach ($this->suggestedDates((string) $draft['start_date'], (int) $draft['days_per_week']) as $date) {
            $draft['deliveries'][$date] = $lines;
        }

        return $this->constrain($draft);
    }

    public function applyPacking(array $draft, string $packing): array
    {
        if ($packing === 'pateela') {
            if ((int) $draft['serving_size'] !== (int) config('noshmark.pateela_persons')) {
                $draft['previous_serving_size'] = (int) $draft['serving_size'];
            }
            $draft['packing'] = 'pateela';
            $draft['serving_size'] = (int) config('noshmark.pateela_persons');
            $draft['per_person_schedule'] = 'same';
        } else {
            $draft['packing'] = 'separate';
            if ((int) $draft['serving_size'] === (int) config('noshmark.pateela_persons')) {
                $previous = (int) ($draft['previous_serving_size'] ?? 4);
                $allowed = config('noshmark.serving_sizes');
                $draft['serving_size'] = in_array($previous, $allowed, true) ? $previous : 4;
            }
        }

        if (! empty($draft['deliveries'])) {
            $lines = $this->basketLines($draft);
            foreach (array_keys($draft['deliveries']) as $date) {
                $draft['deliveries'][$date] = $lines;
            }
        }

        return $this->constrain($draft);
    }

    public function constrain(array $draft): array
    {
        $draft['deliveries'] = is_array($draft['deliveries'] ?? null) ? $draft['deliveries'] : [];

        if (($draft['packing'] ?? 'separate') === 'pateela') {
            $draft['serving_size'] = (int) config('noshmark.pateela_persons');
            $draft['per_person_schedule'] = 'same';

            foreach ($draft['deliveries'] as $date => $lines) {
                if (! is_array($lines) || $lines === []) {
                    continue;
                }
                $first = $lines[0];
                $first['quantity'] = 1;
                $first['persons'] = null;
                $draft['deliveries'][$date] = [$first];
            }
        }

        if (empty($draft['menus_vary']) && count($draft['deliveries']) > 1) {
            ksort($draft['deliveries']);
            $first = reset($draft['deliveries']);
            foreach (array_keys($draft['deliveries']) as $date) {
                $draft['deliveries'][$date] = $first;
            }
        }

        ksort($draft['deliveries']);

        return $draft;
    }
}
