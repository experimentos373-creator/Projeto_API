<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Order extends Model
{
    public $timestamps = true; // Geralmente encomendas têm data de criação

    protected $fillable = [
        'status', 'customer_id', 'date', 'total_price', 'notes',
        'reason_for_cancellation', 'nif', 'address',
        'payment_type', 'payment_ref', 'receipt_url', 'custom'
    ];

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class)->withTrashed();
    }

    public function items(): HasMany
    {
        return $this->hasMany(Order_item::class);
    }
}
