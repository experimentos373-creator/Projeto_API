<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Tshirt_image extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'customer_id', 'category_id', 'name', 'description', 'image_url', 'custom'
    ];

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class)->withTrashed();
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class, 'customer_id', 'id')->withTrashed();
    }

    public function order_items(): HasMany
    {
        return $this->hasMany(Order_item::class, 'tshirt_image_id', 'id');
    }
}
