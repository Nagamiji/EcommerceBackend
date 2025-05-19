<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Download extends Model
{
    protected $fillable = ['order_id', 'product_id', 'download_url', 'expires_at'];
}