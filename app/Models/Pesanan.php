<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Pesanan extends Model
{
    use HasFactory;

    protected $fillable = ['id_pelanggan', 'tanggal'];
    public $timestamps    = false;

    // belongsTo: satu pesanan HANYA punya SATU pelanggan
    public function pelanggan()
    {
        // Parameter 2: 'id_pelanggan' (kolom di tabel pesanans)
        // Parameter 3: 'id' (primary key di tabel pelanggans)
        return $this->belongsTo(Pelanggan::class, 'id_pelanggan', 'id');
    }

    // belongsToMany: satu pesanan bisa punya BANYAK baris detail (item produk)
    public function produk()
    {
        return $this->belongsToMany(
            Produk::class,
            'detail_pesanan',
            'id_pesanan',
            'id_produk'
        )->withPivot('jumlah');
    }
}