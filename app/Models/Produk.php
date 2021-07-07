<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Produk extends Model
{
    use HasFactory;

    protected $table = 'produk';
    protected $primaryKey = 'proId';
    protected $fillable = [
        'proId',
        'nama',
        'harga',
        'stok',
        'rak',
        'tokoId',
        'foto'
    ];

    public function toko(){
        return $this->belongsTo(Toko::class, 'tokoId');
    }
}
