<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable([
    'user_id',
    'number',
    'serving_size',
    'schedule',
    'start_date',
    'menus_vary',
    'packing',
    'days_per_week',
    'per_person_schedule',
    'address',
    'dishes_total',
    'packing_total',
    'delivery_total',
    'grand_total',
    'amount_paid',
    'payment_method',
    'payment_status',
    'payment_reference',
    'paid_at',
    'status',
])]
class Order extends Model
{
    protected function casts(): array
    {
        return [
            'start_date' => 'date',
            'menus_vary' => 'boolean',
            'paid_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    public function deliveries(): HasMany
    {
        return $this->hasMany(Delivery::class)->orderBy('delivery_date');
    }

    public function scheduleLabel(): string
    {
        return (string) config('noshmark.schedules.'.$this->schedule.'.label', $this->schedule);
    }

    public function scheduleSummary(): string
    {
        return (string) config('noshmark.schedules.'.$this->schedule.'.summary', '');
    }

    public function packingLabel(): string
    {
        return (string) config('noshmark.packing.'.$this->packing.'.label', $this->packing);
    }

    public function perPersonLabel(): string
    {
        return (string) config('noshmark.per_person_schedules.'.$this->per_person_schedule, $this->per_person_schedule);
    }

    public function paymentLabel(): string
    {
        return (string) config('noshmark.payment_methods.'.$this->payment_method, $this->payment_method);
    }

    public function refreshTotals(bool $settlePayment = false): void
    {
        $this->unsetRelation('deliveries');
        $this->load('deliveries.items');

        $paidDays = $this->deliveries->where('paid', true);
        $count = $paidDays->count();
        $dishes = (int) $paidDays->sum(fn (Delivery $day) => $day->items->sum('line_total'));
        $packing = $this->packingFee($count);
        $deliveryFee = (int) config('noshmark.delivery_fee') * $count;

        $this->dishes_total = $dishes;
        $this->packing_total = $packing;
        $this->delivery_total = $deliveryFee;
        $this->grand_total = $dishes + $packing + $deliveryFee;

        if ($settlePayment) {
            $this->amount_paid = $this->grand_total;
            $this->payment_status = 'paid';
            $this->paid_at = now();
        }

        $this->save();
    }

    public function packingFee(int $dayCount): int
    {
        if ($dayCount < 1) {
            return 0;
        }

        if ($this->packing === 'pateela') {
            return (int) config('noshmark.pateela_price') * $dayCount;
        }

        return (int) config('noshmark.separate_packing_per_person') * (int) $this->serving_size * $dayCount;
    }

    /**
     * @return array{count: int, dishes: int, packing: int, delivery: int, total: int}
     */
    public function unpaidQuote(): array
    {
        $this->loadMissing('deliveries.items');
        $days = $this->deliveries->where('paid', false);
        $count = $days->count();
        $dishes = (int) $days->sum(fn (Delivery $day) => $day->items->sum('line_total'));
        $packing = $this->packingFee($count);
        $delivery = (int) config('noshmark.delivery_fee') * $count;

        return [
            'count' => $count,
            'dishes' => $dishes,
            'packing' => $packing,
            'delivery' => $delivery,
            'total' => $dishes + $packing + $delivery,
        ];
    }
}
