<?php

namespace App\Http\Controllers;

use App\Models\Delivery;
use App\Models\MenuItem;
use App\Models\Order;
use App\Services\OrderCalculator;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class CustomerOrderController extends Controller
{
    public function index(): View
    {
        $orders = auth()->user()->orders()->with('deliveries')->latest()->get();

        return view('orders.index', compact('orders'));
    }

    public function show(Order $order): View
    {
        $this->owns($order);
        $order->load('deliveries.items.menuItem', 'items');

        return view('orders.show', [
            'order' => $order,
            'menu' => MenuItem::query()->where('is_active', true)->orderBy('name')->get(),
            'due' => $order->unpaidQuote(),
        ]);
    }

    public function updateDelivery(Request $request, Order $order, Delivery $delivery, OrderCalculator $calculator): RedirectResponse
    {
        $this->owns($order);
        abort_unless($delivery->order_id === $order->id, 404);

        if ($delivery->isLocked()) {
            return back()->withErrors(['menu' => 'Menus can only be changed at least '.config('noshmark.lead_days').' days before delivery.']);
        }

        $data = $request->validate([
            'lines' => ['required', 'array', 'min:1'],
            'lines.*.menu_item_id' => ['required', 'integer'],
            'lines.*.quantity' => ['nullable', 'integer', 'min:1', 'max:'.config('noshmark.max_quantity')],
            'lines.*.persons' => ['nullable', 'integer', 'min:1', 'max:30'],
            'confirm_payment' => ['nullable', 'boolean'],
        ]);

        $mode = $order->packing === 'pateela' ? 'same' : $order->per_person_schedule;
        $raw = $data['lines'];

        if ($order->packing === 'pateela') {
            $raw = [[
                'menu_item_id' => (int) $raw[0]['menu_item_id'],
                'quantity' => 1,
                'persons' => null,
            ]];
        }

        $menu = $calculator->menuFor(array_column($raw, 'menu_item_id'));
        $priced = $calculator->priceLines($mode, (int) $order->serving_size, $raw, $menu);

        if ($priced['errors'] !== []) {
            return back()->withErrors(['menu' => $priced['errors'][0]])->withInput();
        }

        try {
            DB::transaction(function () use ($order, $delivery, $priced, $request) {
                $delivery->items()->delete();
                foreach ($priced['lines'] as $line) {
                    $delivery->items()->create([
                        'menu_item_id' => $line['menu_item_id'],
                        'name' => $line['name'],
                        'unit_price' => $line['unit_price'],
                        'quantity' => $line['quantity'],
                        'persons' => $line['persons'],
                        'line_total' => $line['line_total'],
                    ]);
                }

                $order->refreshTotals(false);
                $order->refresh();

                if ($delivery->paid && (int) $order->grand_total !== (int) $order->amount_paid) {
                    if (! $request->boolean('confirm_payment')) {
                        throw ValidationException::withMessages([
                            'confirm_payment' => 'This menu change updates the total. 100% payment of the new amount (PKR '.number_format($order->grand_total).') is required.',
                        ]);
                    }

                    $order->amount_paid = $order->grand_total;
                    $order->payment_status = 'paid';
                    $order->paid_at = now();
                    $order->payment_reference = 'PAY-'.strtoupper(Str::random(8));
                    $order->save();
                }
            });
        } catch (ValidationException $exception) {
            return back()->withErrors($exception->errors())->withInput();
        }

        return back()->with('status', 'Menu updated for '.$delivery->delivery_date->format('D, j M').'. The order stays paid in full.');
    }

    public function extend(Order $order): RedirectResponse
    {
        $this->owns($order);
        $order->load('deliveries.items');

        if ($order->deliveries->contains(fn (Delivery $day) => ! $day->paid)) {
            return back()->withErrors(['cycle' => 'Pay the next cycle before adding another.']);
        }

        $min = now()->startOfDay()->addDays((int) config('noshmark.lead_days'));
        $last = $order->deliveries->max('delivery_date');
        $created = 0;

        DB::transaction(function () use ($order, $min, $last, &$created) {
            foreach ($order->deliveries as $delivery) {
                $next = $this->shift($delivery->delivery_date, $order->schedule);
                if ($next->lte($last) || $next->lt($min)) {
                    continue;
                }
                if ($order->deliveries()->whereDate('delivery_date', $next->toDateString())->exists()) {
                    continue;
                }

                $copy = $order->deliveries()->create([
                    'delivery_date' => $next->toDateString(),
                    'paid' => false,
                    'status' => 'scheduled',
                ]);

                foreach ($delivery->items as $item) {
                    $copy->items()->create([
                        'menu_item_id' => $item->menu_item_id,
                        'name' => $item->name,
                        'unit_price' => $item->unit_price,
                        'quantity' => $item->quantity,
                        'persons' => $item->persons,
                        'line_total' => $item->line_total,
                    ]);
                }
                $created++;
            }
        });

        if ($created === 0) {
            return back()->withErrors(['cycle' => 'The next cycle is already on this order, or those dates are inside the 2-day lead time.']);
        }

        return back()->with('status', 'Next '.$order->scheduleLabel().' deliveries added. Pay 100% to confirm them.');
    }

    public function payCycle(Order $order): RedirectResponse
    {
        $this->owns($order);
        $order->load('deliveries');

        $unpaid = $order->deliveries->where('paid', false);
        if ($unpaid->isEmpty()) {
            return back()->withErrors(['cycle' => 'There is no unpaid cycle to pay.']);
        }

        $min = now()->startOfDay()->addDays((int) config('noshmark.lead_days'));
        if ($unpaid->contains(fn (Delivery $day) => $day->delivery_date->copy()->startOfDay()->lt($min))) {
            return back()->withErrors(['cycle' => 'A delivery in this cycle is inside the 2-day lead time. Remove it or move the date by editing the order.']);
        }

        DB::transaction(function () use ($order) {
            $order->deliveries()->where('paid', false)->update(['paid' => true]);
            $order->refreshTotals(true);
            $order->payment_reference = 'PAY-'.strtoupper(Str::random(8));
            $order->save();
        });

        return back()->with('status', 'Next cycle paid in full. Your delivery schedule is confirmed.');
    }

    private function owns(Order $order): void
    {
        abort_unless($order->user_id === auth()->id(), 403);
    }

    private function shift(Carbon $date, string $schedule): Carbon
    {
        return match ($schedule) {
            'every_15_days' => $date->copy()->addDays(15),
            'monthly' => $date->copy()->addMonthNoOverflow(),
            default => $date->copy()->addWeek(),
        };
    }
}
