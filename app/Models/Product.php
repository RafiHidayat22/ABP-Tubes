<?php
// app/Models/Product.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
        'image',
        'efficiency',
        'power_output',
        'price',
        'stock',
        'is_active'
    ];

    protected $casts = [
        'efficiency' => 'decimal:2',
        'power_output' => 'decimal:2',
        'price' => 'decimal:2',
        'is_active' => 'boolean'
    ];

    public function carts()
    {
        return $this->hasMany(Cart::class);
    }

    public function orderItems()
    {
        return $this->hasMany(OrderItem::class);
    }
}