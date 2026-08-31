<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pelanggans', function (Blueprint $table) {
            $table->id(); // Membuat kolom 'id' sebagai Primary Key Auto Increment
            $table->string('nama_pelanggan');
            $table->text('alamat');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        // Disamakan nama tabelnya menjadi 'pelanggans'
        Schema::dropIfExists('pelanggans'); 
    }
};