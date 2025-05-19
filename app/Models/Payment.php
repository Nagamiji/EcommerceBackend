<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Payment extends Model
{
    protected $fillable = ['order_id', 'transaction_id', 'payment_status', 'amount', 'payment_method'];

    // Disable timestamps since updated_at is missing
    public $timestamps = false;

    public function order()
    {
        return $this->belongsTo(Order::class);
    }
}