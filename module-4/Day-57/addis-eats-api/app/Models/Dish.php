<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Dish extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name',
        'restaurant_id',
        'price',
        'is_fasting',
        'area',
        'cost_price',
        'supplier_note',
    ];

    protected function casts(): array
    {
        return [
            'price'      => 'integer',
            'cost_price' => 'integer',
            'is_fasting' => 'boolean',
        ];
    }

    public function restaurant(): BelongsTo
    {
        return $this->belongsTo(Restaurant::class);
    }

    public function scopeInArea(Builder $query, ?string $area): Builder
    {
        return $query->when($area, fn (Builder $q) => $q->where('area', $area));
    }

    public function scopeUnder(Builder $query, ?int $santim): Builder
    {
        return $query->when($santim, fn (Builder $q) => $q->where('price', '<=', $santim));
    }

    public function scopeFasting(Builder $query, bool $fasting = false): Builder
    {
        return $query->when($fasting, fn (Builder $q) => $q->where('is_fasting', true));
    }
}