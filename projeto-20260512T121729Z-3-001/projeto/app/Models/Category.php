<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Category extends Model
{
    use SoftDeletes;

    public $timestamps = false;

    protected $fillable = ['name', 'image_url', 'custom'];

    public function tshirt_images(): HasMany
    {
        return $this->hasMany(Tshirt_image::class, 'category_id', 'id');
    }
}
