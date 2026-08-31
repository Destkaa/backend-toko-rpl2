<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Kategori extends Model
{
    use HasFactory;

    protected $table = 'kategoris';
    
    // PERBAIKAN: Ubah dari 'id_kategori' menjadi 'id'
    protected $primaryKey = 'id'; 

    protected $fillable = [
        'nama_kategori',
    ];

    public function produk()
    {
        return $this->hasMany(Produk::class, 'id_kategori', 'id');
    }
}