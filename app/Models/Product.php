<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

// class Product extends Model
// {
//     use HasFactory;
// }
class Product extends Model
{
    protected $fillable = [
        'name',
        'type',
        'color',
        'size',
        'quantity',
        'price',
        'barcode',
        'image',
        'user_id'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
