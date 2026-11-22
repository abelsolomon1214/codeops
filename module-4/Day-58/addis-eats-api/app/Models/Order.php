<?php

namespace App\Models;

use App\Enums\OrderStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Order extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'restaurant_id',
        'area',
        'phone',
        'status',
        'subtotal_santim',
        'delivery_fee_santim',
        'total_santim',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'status'              => OrderStatus::class,
            'subtotal_santim'     => 'integer',
            'delivery_fee_santim' => 'integer',
            'total_santim'        => 'integer',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function restaurant(): BelongsTo
    {
        return $this->belongsTo(Restaurant::class);
    }

    public function lines(): HasMany
    {
        return $this->hasMany(OrderLine::class);
    }

    public function dishes(): BelongsToMany
    {
        return $this->belongsToMany(Dish::class, 'order_lines')
            ->withPivot(['qty', 'unit_price_santim', 'line_total_santim'])
            ->withTimestamps();
    }
}
