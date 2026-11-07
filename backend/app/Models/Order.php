<?php

namespace App\Models;

use MongoDB\Laravel\Eloquent\Model;

class Order extends Model
{
    protected $connection = 'mongodb';
    protected $collection = 'orders';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'order_number',
        'user_id',
        'items',             // Embedded array: [{product_id, name, price, quantity, subtotal}]
        'subtotal',
        'tax_amount',
        'shipping_fee',
        'total_amount',
        'status',            // 'pending', 'processing', 'shipped', 'delivered', 'cancelled'
        'shipping_address',  // Array: {street, city, state, postal_code, country}
        'payment_method',    // 'credit_card', 'paypal', 'bank_transfer', 'cod'
        'payment_status',    // 'unpaid', 'paid', 'refunded'
        'notes',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'items' => 'array',
        'subtotal' => 'float',
        'tax_amount' => 'float',
        'shipping_fee' => 'float',
        'total_amount' => 'float',
        'shipping_address' => 'array',
    ];

    /**
     * The customer who placed this order.
     */
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}

