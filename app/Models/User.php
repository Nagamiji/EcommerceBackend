<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens; // Add this

class User extends Authenticatable
{
    use HasApiTokens, Notifiable; // Add HasApiTokens here

    protected $fillable = [
        'name', 'email', 'password', 'is_admin', 'role',
    ];

    protected $hidden = [
        'password', 'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'is_admin' => 'boolean',
    ];

    public function products()
    {
        return $this->hasMany(Product::class);
    }

    public function isCustomer()
    {
        return $this->role === 'customer';
    }

    public function isSeller()
    {
        return $this->role === 'seller' || $this->is_admin;
    }

    public function isAdmin()
    {
        return $this->is_admin == 1;
    }
}