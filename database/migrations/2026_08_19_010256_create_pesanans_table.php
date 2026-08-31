<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pesanans', function (Blueprint $table) {
            $table->id('id_pesanan');
            $table->foreignId('id_pelanggan')->nullable()->constrained('pelanggans');
            $table->date('tanggal');
        });

        Schema::create('detail_pesanan', function (Blueprint $table) {
            $table->id('id');
            // 1. Menambahkan 'id_pesanan' agar merujuk ke primary key kustom tabel pesanans
            $table->foreignId('id_pesanan')->nullable()->constrained('pesanans', 'id_pesanan');
            $table->foreignId('id_produk')->nullable()->constrained('produks');
            $table->integer('jumlah');
        });
    }

    public function down(): void
    {
        // 2. Membalik urutan drop (detail_pesanan dulu baru pesanans)
        // 3. Memperbaiki typo nama tabel 'pesanan' menjadi 'pesanans'
        Schema::dropIfExists('detail_pesanan');
        Schema::dropIfExists('pesanans');
    }
};