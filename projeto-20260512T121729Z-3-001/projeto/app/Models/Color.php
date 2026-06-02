<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Storage;

class Color extends Model
{
    use SoftDeletes;

    // Se a tua chave primária não for 'id', define assim:
    protected $primaryKey = 'code';

    // Como a chave é uma string (code), deves desativar o incremento
    public $incrementing = false;
    protected $keyType = 'string';

    public $timestamps = false;

    protected $fillable = [
        'code',
        'name',
        'custom'
    ];

    public function getTshirtBaseUrlAttribute()
    {
        if (Storage::disk('public')->exists('tshirt_base/' . $this->code . '.png')) {
            return asset('storage/tshirt_base/' . $this->code . '.png');
        }
        if (Storage::disk('public')->exists('tshirt_base/' . $this->code . '.jpg')) {
            return asset('storage/tshirt_base/' . $this->code . '.jpg');
        }
        if (Storage::disk('public')->exists('tshirt_base/' . $this->code . '.jpeg')) {
            return asset('storage/tshirt_base/' . $this->code . '.jpeg');
        }
        return asset('storage/tshirt_base/plain_white.png');
    }
}
