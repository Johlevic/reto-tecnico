<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('allowance_charges', function (Blueprint $table) {
            $table->id('id');
            $table->unsignedBigInteger('voucher_id'); // Usar uuid aquí
            $table->boolean('charge_indicator')->default(false);  // Si usas BOOLEAN// True para cargo, false para descuento
            $table->string('reason_code')->nullable(); // Código del motivo
            $table->decimal('amount', 15, 2)->default(0); // Monto del cargo o descuento
            $table->decimal('base_amount', 15, 2)->default(0); // Monto base
            $table->timestamps();

            $table->foreign('voucher_id')->references('id')->on('vouchers')->onDelete('cascade'); // Relación con vouchers
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('allowance_charges');
    }
};
