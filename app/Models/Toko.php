<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Toko extends Model
{
    use HasFactory;

    protected $table = 'toko';
    protected $primaryKey = 'tokoId';
    protected $fillable = [
        'tokoId',
        'nama',
        'alamat',
        'kota',
        'provinsi',
        'userId',
        'foto'
    ];

    public function user(){
        return $this->belongsTo(User::class, 'userId');
    }

    public function produk(){
        return $this->hasMany(Produk::class, 'tokoId');
    }
}
