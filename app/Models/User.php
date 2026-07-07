<?php

namespace App\Models;

use App\Enums\UserRole;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, HasUuids, Notifiable;

    protected $fillable = [
        'email',
        'password',
        'nom',
        'prenom',
        'telephone',
        'service',
        'role',
        'actif',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'password' => 'hashed',
            'actif' => 'boolean',
            'role' => UserRole::class,
        ];
    }

    public function getRouteKeyName(): string
    {
        return 'id';
    }

    public function fullName(): string
    {
        return trim($this->prenom.' '.$this->nom);
    }

    public static function findByIdentifiant(string $identifiant): ?self
    {
        $identifiant = trim($identifiant);

        if (filter_var($identifiant, FILTER_VALIDATE_EMAIL)) {
            return static::query()
                ->whereRaw('LOWER(email) = ?', [mb_strtolower($identifiant)])
                ->first();
        }

        $digits = preg_replace('/\D/', '', $identifiant);

        if ($digits === '') {
            return null;
        }

        return static::query()
            ->whereNotNull('telephone')
            ->where('telephone', '!=', '')
            ->get()
            ->first(fn (self $user) => preg_replace('/\D/', '', $user->telephone) === $digits);
    }
}
