<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('booking', function (Blueprint $table) {
            $table->bigIncrements('id_booking');
            $table->string('kode', 12)->unique();
            $table->unsignedBigInteger('id_pelanggan');
            $table->unsignedBigInteger('id_unit');
            $table->unsignedBigInteger('id_staff')->nullable();
            $table->date('tgl_main');
            $table->time('mulai');
            $table->time('selesai');
            $table->unsignedTinyInteger('durasi_jam');
            $table->decimal('total', 12, 2);
            $table->enum('status', ['PENDING', 'CONFIRMED', 'CHECKIN', 'SELESAI', 'BATAL'])->default('PENDING');
            $table->timestamps();

            $table->foreign('id_pelanggan')->references('id_pelanggan')->on('pelanggan')->restrictOnDelete();
            $table->foreign('id_unit')->references('id_unit')->on('unit_ps')->restrictOnDelete();
            $table->foreign('id_staff')->references('id_staff')->on('staff')->nullOnDelete();

            // Mempercepat pengecekan overlap booking per unit & tanggal
            $table->index(['id_unit', 'tgl_main', 'mulai', 'selesai']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('booking');
    }
};
