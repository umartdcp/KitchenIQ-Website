<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['category_id', 'name', 'slug', 'description', 'price_per_person', 'image', 'is_active', 'sort'])]
class MenuItem extends Model
{
    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'price_per_person' => 'integer',
        ];
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }
}
