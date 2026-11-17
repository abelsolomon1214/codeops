<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Dish extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name',
        'restaurant',
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
}