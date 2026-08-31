<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Pelanggan extends Model
{
    use HasFactory;

    protected $fillable = ['nama_pelanggan', 'alamat'];
		public $timestamps    = true;

    // Satu pelanggan bisa punya banyak pesanan
    public function pesanan()
    {
        return $this->hasMany(Pesanan::class, 'id_pelanggan');
    }
}