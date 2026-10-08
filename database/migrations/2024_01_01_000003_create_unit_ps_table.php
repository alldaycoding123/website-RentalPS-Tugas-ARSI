<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('unit_ps', function (Blueprint $table) {
            $table->bigIncrements('id_unit');
            $table->string('kode_unit', 20)->unique();
            $table->string('nama', 100);
            $table->string('tipe', 20); // PS4 / PS5
            $table->string('ruang', 50);
            $table->string('tv', 150);
            $table->decimal('harga_jam', 10, 2);
            $table->enum('status', ['TERSEDIA', 'MAINTENANCE', 'DIGUNAKAN'])->default('TERSEDIA');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('unit_ps');
    }
};
