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
        Schema::create('voucher_lines', function (Blueprint $table) {
            $table->id('id')->primary();
            $table->unsignedBigInteger('voucher_id');
            $table->string('line_id')->nullable(); // ID de la línea
            $table->text('description')->nullable(); // Descripción del producto o servicio
            $table->decimal('quantity', 15, 2)->default(0); // Cantidad
            $table->decimal('unit_price', 15, 2)->default(0); // Precio unitario
            $table->decimal('line_extension_amount', 15, 2)->default(0); // Cantidad * Precio unitario
            $table->decimal('tax_amount', 15, 2)->default(0); // Impuesto
            $table->string('item_id')->nullable(); // Código del producto o servicio
            $table->timestamps();

            $table->foreign('voucher_id')->references('id')->on('vouchers')->onDelete('cascade'); // Relación con vouchers
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('voucher_lines');
    }
};
