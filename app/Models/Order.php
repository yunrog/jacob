<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    use HasFactory;

    protected $guarded = [];

    // Relasi ke item transaksi/pesanan
    public function items()
    {
        return $this->hasMany(OrderItem::class);
    }

    // Relasi ke pelanggan/user
    public function customer()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}