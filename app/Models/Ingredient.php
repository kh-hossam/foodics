<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Ingredient extends Model
{
    use HasFactory;

    protected $guarded = [];

    public function merchant(): BelongsTo
    {
        return $this->belongsTo(Merchant::class);
    }


    // store original stock in grams in database, and retrieve it as kg
    protected function originalStockInKg(): Attribute
    {
        return Attribute::make(
            get: fn (mixed $value, array $attributes) => ($attributes['original_stock'] / 1000),
            set: fn (string $value) => [
                'original_stock' => $value * 1000,
            ]
        );
    }
}
