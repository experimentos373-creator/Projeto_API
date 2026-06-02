<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Str;
use Laravel\Fortify\TwoFactorAuthenticatable;
use Illuminate\Database\Eloquent\Relations\HasOne;

class User extends Authenticatable implements MustVerifyEmail
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable, TwoFactorAuthenticatable, SoftDeletes;

    // Movido os atributos para propriedades da classe para evitar conflitos de sintaxe
    protected $fillable = [
        'name',
        'email',
        'password',
        'user_type',
        'gender',
        'blocked',
        'photo_url',
        'custom'
    ];

    protected $hidden = [
        'password',
        'two_factor_secret',
        'two_factor_recovery_codes',
        'remember_token'
    ];

    /**
     * Relacionamento: User tem um Customer (Perfil de Cliente)
     * No seu diagrama, o ID do Customer é o mesmo que o ID do User.
     */
    public function customer(): HasOne
    {
        return $this->hasOne(Customer::class, 'id', 'id');
    }

    /**
     * Get the attributes that should be cast.
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    /**
     * Get the user's initials
     */
    public function initials(): string
    {
        return Str::of($this->name)
            ->explode(' ')
            ->take(2)
            ->map(fn ($word) => Str::substr($word, 0, 1))
            ->implode('');
    }

    /**
     * Accessor for user's photo_url
     */
    public function getPhotoUrlAttribute($value)
    {
        if (!$value) {
            return null;
        }

        if (str_starts_with($value, 'http://') || str_starts_with($value, 'https://')) {
            return $value;
        }

        // Se o valor não contiver a subpasta 'photos/', adiciona-a
        if (!str_starts_with($value, 'photos/')) {
            $value = 'photos/' . $value;
        }

        // Se o ficheiro físico existir no storage público, devolve a rota
        if (\Illuminate\Support\Facades\Storage::disk('public')->exists($value)) {
            return $value;
        }

        return null;
    }
}
