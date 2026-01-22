<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Transaction extends Model
{
    use HasFactory;

    protected $fillable = [
        'transaction_code',
        'total_amount',
        'pay_amount',
        'change_amount',
        'payment_method',
    ];

    public function items()
    {
        return $this->hasMany(TransactionItem::class);
    }
}
