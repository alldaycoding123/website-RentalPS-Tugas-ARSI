<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pembayaran', function (Blueprint $table) {
            $table->bigIncrements('id_bayar');
            $table->unsignedBigInteger('id_booking')->unique(); // satu booking = satu pembayaran
            $table->unsignedBigInteger('id_staff')->nullable();
            $table->enum('metode', ['TRANSFER_BANK', 'E_WALLET', 'BAYAR_DI_TEMPAT']);
            $table->decimal('nominal', 12, 2);
            $table->enum('status', ['BELUM_BAYAR', 'LUNAS'])->default('BELUM_BAYAR');
            $table->dateTime('waktu')->nullable();
            $table->timestamps();

            $table->foreign('id_booking')->references('id_booking')->on('booking')->cascadeOnDelete();
            $table->foreign('id_staff')->references('id_staff')->on('staff')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pembayaran');
    }
};
