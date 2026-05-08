<?php

namespace App\Models;

use Filament\Models\Contracts\FilamentUser;
use Filament\Models\Contracts\HasName;
use Filament\Panel;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable implements FilamentUser, HasName
{
    use HasApiTokens, HasFactory;

    protected $fillable = [
        'email',
        'password',
        'wallet',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'wallet' => 'decimal:2',
    ];

    public function canAccessPanel(Panel $panel): bool
    {
        return true; // change en $this->is_admin si tu veux restreindre
    }

    public function getFilamentName(): string
    {
        return $this->email;
    }

    public function transactions()
    {
        return $this->hasMany(Transaction::class)->latest();
    }

    public function withdrawalRequests()
    {
        return $this->hasMany(WithdrawalRequest::class)->latest();
    }
}