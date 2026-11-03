<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['order_id', 'delivery_date', 'paid', 'status'])]
class Delivery extends Model
{
    protected function casts(): array
    {
        return [
            'delivery_date' => 'date',
            'paid' => 'boolean',
        ];
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(DeliveryItem::class);
    }

    public function isLocked(): bool
    {
        $min = now()->startOfDay()->addDays((int) config('noshmark.lead_days'));

        return $this->delivery_date->copy()->startOfDay()->lt($min);
    }
}
