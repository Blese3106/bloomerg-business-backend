<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
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

    public function transactions()
    {
        return $this->hasMany(Transaction::class)->latest();
    }

    public function withdrawalRequests()
    {
        return $this->hasMany(WithdrawalRequest::class)->latest();
    }
}