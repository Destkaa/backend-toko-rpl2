<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Pelanggan extends Model
{
    use HasFactory;

    protected $table = 'pelanggans';

    protected $fillable = [
        'nama_pelanggan',
        'alamat',
    ];

    public $timestamps = true;

    // Relasi: Satu pelanggan memiliki banyak pesanan
    public function pesanan()
    {
        // Parameter: (Model Tujuan, Foreign Key di tabel pesanans, Local Key di tabel pelanggans)
        return $this->hasMany(Pesanan::class, 'id_pelanggan', 'id');
    }
}