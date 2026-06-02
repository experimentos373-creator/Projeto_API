<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Order_item extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'order_id', 'tshirt_image_id', 'color_code', 'size', 'qty', 'unit_price', 'sub_total', 'custom'
    ];

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function tshirt_image(): BelongsTo
    {
        return $this->belongsTo(Tshirt_image::class)->withTrashed();
    }

    public function color(): BelongsTo
    {
        return $this->belongsTo(Color::class, 'color_code', 'code')->withTrashed();
    }
}
