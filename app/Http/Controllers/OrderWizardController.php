<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\MenuItem;
use App\Models\Order;
use App\Services\OrderCalculator;
use App\Services\OrderDraft;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\View\View;

class OrderWizardController extends Controller
{
    public function menu(OrderDraft $drafts, OrderCalculator $calculator): View
    {
        $draft = $drafts->get();
        $items = MenuItem::query()->with('category')->where('is_active', true)->orderBy('sort')->get();

        return view('order.menu', [
            'draft' => $draft,
            'categories' => Category::query()->orderBy('sort')->get(),
            'items' => $items,
            'basket' => $calculator->perDeliveryBasket($draft['items'], (int) $draft['serving_size']),
        ]);
    }

    public function storeMenu(Request $request, OrderDraft $drafts): RedirectResponse
    {
        $allowed = config('noshmark.serving_sizes');
        $pateelaPersons = (int) config('noshmark.pateela_persons');

        $data = $request->validate([
            'serving_size' => ['required', 'integer', 'in:'.implode(',', [...$allowed, $pateelaPersons])],
            'qty' => ['nullable', 'array'],
            'qty.*' => ['nullable', 'integer', 'min:0', 'max:'.config('noshmark.max_quantity')],
        ]);

        $draft = $drafts->get();
        $serving = (int) $data['serving_size'];

        if ($serving === $pateelaPersons) {
            $draft = $drafts->applyPacking($draft, 'pateela');
        } else {
            if ($draft['packing'] === 'pateela') {
                $draft['packing'] = 'separate';
            }
            $draft['previous_serving_size'] = $serving;
            $draft['serving_size'] = $serving;
        }

        $draft['items'] = $drafts->normalizeItems($data['qty'] ?? []);

        if ($draft['items'] === []) {
            return back()->withErrors(['qty' => 'Select at least one menu item and a quantity.'])->withInput();
        }

        $validIds = MenuItem::query()->where('is_active', true)->whereIn('id', array_keys($draft['items']))->pluck('id')->all();
        $draft['items'] = array_intersect_key($draft['items'], array_flip($validIds));

        if ($draft['items'] === []) {
            return back()->withErrors(['qty' => 'Select at least one menu item and a quantity.'])->withInput();
        }

        if (! empty($draft['deliveries'])) {
            $lines = $drafts->basketLines($draft);
            foreach (array_keys($draft['deliveries']) as $date) {
                $draft['deliveries'][$date] = $lines;
            }
        }

        $drafts->put($drafts->constrain($draft));

        return redirect()->route('order.schedule');
    }

    public function schedule(OrderDraft $drafts): View|RedirectResponse
    {
        $draft = $drafts->get();
        if ($draft['items'] === []) {
            return redirect()->route('order.menu');
        }

        $ids = array_map('intval', array_keys($draft['items']));
        $menu = MenuItem::query()->whereIn('id', $ids)->get()->keyBy('id');

        return view('order.schedule', [
            'draft' => $draft,
            'menu' => $menu,
            'minDate' => $drafts->minDate()->toDateString(),
        ]);
    }

    public function storeSchedule(Request $request, OrderDraft $drafts): RedirectResponse
    {
        $draft = $drafts->get();
        if ($draft['items'] === []) {
            return redirect()->route('order.menu');
        }

        $data = $request->validate([
            'schedule' => ['required', 'in:'.implode(',', array_keys(config('noshmark.schedules')))],
            'start_date' => ['required', 'date', 'after_or_equal:'.$drafts->minDate()->toDateString()],
            'menus_vary' => ['nullable', 'boolean'],
        ], [
            'start_date.after_or_equal' => 'The start date must be at least '.config('noshmark.lead_days').' days from today.',
        ]);

        $draft['schedule'] = $data['schedule'];
        $draft['start_date'] = $data['start_date'];
        $draft['menus_vary'] = $request->boolean('menus_vary');
        $draft['deliveries'] = [];
        $drafts->put($draft);

        return redirect()->route('order.packing');
    }

    public function packing(OrderDraft $drafts, OrderCalculator $calculator): View|RedirectResponse
    {
        $draft = $drafts->get();
        if ($draft['items'] === []) {
            return redirect()->route('order.menu');
        }

        $separateServing = $draft['packing'] === 'pateela'
            ? (int) ($draft['previous_serving_size'] ?: 4)
            : (int) $draft['serving_size'];

        return view('order.packing', [
            'draft' => $draft,
            'separateServing' => $separateServing,
            'separateBasket' => $calculator->perDeliveryBasket($draft['items'], $separateServing),
            'pateelaBasket' => $calculator->perDeliveryBasket($draft['items'], (int) config('noshmark.pateela_persons')),
        ]);
    }

    public function storePacking(Request $request, OrderDraft $drafts): RedirectResponse
    {
        $draft = $drafts->get();
        if ($draft['items'] === []) {
            return redirect()->route('order.menu');
        }

        $data = $request->validate([
            'packing' => ['required', 'in:separate,pateela'],
        ]);

        $drafts->put($drafts->applyPacking($draft, $data['packing']));

        return redirect()->route('order.delivery');
    }

    public function delivery(OrderDraft $drafts, OrderCalculator $calculator): View|RedirectResponse
    {
        $draft = $drafts->get();
        if ($draft['items'] === []) {
            return redirect()->route('order.menu');
        }

        $draft = $drafts->ensureDeliveries($draft);
        $drafts->put($draft);

        $items = MenuItem::query()->where('is_active', true)->orderBy('sort')->get();

        return view('order.delivery', [
            'draft' => $draft,
            'items' => $items,
            'quote' => $calculator->quote($draft),
            'minDate' => $drafts->minDate()->toDateString(),
        ]);
    }

    public function storeDelivery(Request $request, OrderDraft $drafts, OrderCalculator $calculator): RedirectResponse
    {
        $draft = $drafts->get();
        if ($draft['items'] === []) {
            return redirect()->route('order.menu');
        }

        $data = $request->validate([
            'days_per_week' => ['required', 'integer', 'min:1', 'max:7'],
            'per_person_schedule' => ['required', 'in:same,personalized'],
            'address' => ['required', 'string', 'min:8', 'max:500'],
            'delivery_payload' => ['required', 'json'],
        ]);

        $decoded = json_decode($data['delivery_payload'], true);
        $deliveries = [];

        foreach (($decoded['deliveries'] ?? []) as $date => $lines) {
            if (! is_string($date) || ! is_array($lines)) {
                continue;
            }
            $clean = [];
            foreach ($lines as $line) {
                if (! is_array($line)) {
                    continue;
                }
                $clean[] = [
                    'menu_item_id' => (int) ($line['menu_item_id'] ?? 0),
                    'quantity' => (int) ($line['quantity'] ?? 1),
                    'persons' => array_key_exists('persons', $line) && $line['persons'] !== null ? (int) $line['persons'] : null,
                ];
            }
            if ($clean !== []) {
                $deliveries[$date] = $clean;
            }
        }

        $draft['days_per_week'] = (int) $data['days_per_week'];
        $draft['per_person_schedule'] = $draft['packing'] === 'pateela' ? 'same' : $data['per_person_schedule'];
        $draft['address'] = $data['address'];
        $draft['deliveries'] = $deliveries;
        $draft = $drafts->constrain($draft);
        $drafts->put($draft);

        $quote = $calculator->quote($draft);
        if ($quote['errors'] !== []) {
            return back()->withErrors(['delivery' => $quote['errors'][0]])->with('quote_errors', $quote['errors'])->withInput();
        }

        return redirect()->route('order.review');
    }

    public function review(OrderDraft $drafts, OrderCalculator $calculator): View|RedirectResponse
    {
        $draft = $drafts->get();
        if ($draft['items'] === []) {
            return redirect()->route('order.menu');
        }
        if ($draft['deliveries'] === []) {
            return redirect()->route('order.delivery');
        }

        $quote = $calculator->quote($draft);
        if ($quote['errors'] !== []) {
            return redirect()->route('order.delivery')->withErrors(['delivery' => $quote['errors'][0]])->with('quote_errors', $quote['errors']);
        }

        $menu = MenuItem::query()->whereIn('id', array_map('intval', array_keys($draft['items'])))->get()->keyBy('id');

        return view('order.review', compact('draft', 'quote', 'menu'));
    }

    public function pay(Request $request, OrderDraft $drafts, OrderCalculator $calculator): RedirectResponse
    {
        $draft = $drafts->constrain($drafts->get());
        $quote = $calculator->quote($draft);

        if ($quote['errors'] !== []) {
            return redirect()->route('order.delivery')->withErrors(['delivery' => $quote['errors'][0]]);
        }

        $data = $request->validate([
            'payment_method' => ['required', 'in:'.implode(',', array_keys(config('noshmark.payment_methods')))],
        ]);

        $order = DB::transaction(function () use ($draft, $quote, $data) {
            $order = Order::create([
                'user_id' => auth()->id(),
                'number' => 'TMP-'.strtoupper(Str::random(10)),
                'serving_size' => $quote['serving_size'],
                'schedule' => $draft['schedule'],
                'start_date' => $draft['start_date'],
                'menus_vary' => (bool) $draft['menus_vary'],
                'packing' => $quote['packing'],
                'days_per_week' => (int) $draft['days_per_week'],
                'per_person_schedule' => $quote['per_person_schedule'],
                'address' => $draft['address'],
                'dishes_total' => $quote['dishes_total'],
                'packing_total' => $quote['packing_total'],
                'delivery_total' => $quote['delivery_total'],
                'grand_total' => $quote['grand_total'],
                'amount_paid' => $quote['grand_total'],
                'payment_method' => $data['payment_method'],
                'payment_status' => 'paid',
                'payment_reference' => 'PAY-'.strtoupper(Str::random(8)),
                'paid_at' => now(),
                'status' => 'confirmed',
            ]);

            $order->number = 'NM-'.str_pad((string) $order->id, 5, '0', STR_PAD_LEFT);
            $order->save();

            $menu = MenuItem::query()->whereIn('id', array_map('intval', array_keys($draft['items'])))->get()->keyBy('id');
            foreach ($draft['items'] as $id => $quantity) {
                $item = $menu->get((int) $id);
                if (! $item) {
                    continue;
                }
                $order->items()->create([
                    'menu_item_id' => $item->id,
                    'name' => $item->name,
                    'unit_price' => $item->price_per_person,
                    'quantity' => (int) $quantity,
                ]);
            }

            foreach ($quote['days'] as $day) {
                $delivery = $order->deliveries()->create([
                    'delivery_date' => $day['date'],
                    'paid' => true,
                    'status' => 'scheduled',
                ]);

                foreach ($day['lines'] as $line) {
                    $delivery->items()->create([
                        'menu_item_id' => $line['menu_item_id'],
                        'name' => $line['name'],
                        'unit_price' => $line['unit_price'],
                        'quantity' => $line['quantity'],
                        'persons' => $line['persons'],
                        'line_total' => $line['line_total'],
                    ]);
                }
            }

            return $order;
        });

        $drafts->forget();

        return redirect()->route('order.confirmed', $order);
    }

    public function confirmed(Order $order): View
    {
        abort_unless($order->user_id === auth()->id(), 403);
        $order->load('deliveries.items', 'items');

        return view('order.confirmed', compact('order'));
    }
}
