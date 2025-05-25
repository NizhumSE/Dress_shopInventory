<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

// class Invoice extends Model
// {
//     use HasFactory;
// }

class Invoice extends Model
{
    protected $fillable = ['invoice_number', 'user_id', 'total', 'customer_info'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function products()
    {
        return $this->belongsToMany(Product::class)
            ->withPivot('quantity', 'price');
    }
}
