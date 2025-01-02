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
        Schema::create('tax_totals', function (Blueprint $table) {
            $table->id('id');
            $table->unsignedBigInteger('voucher_id');
            $table->decimal('tax_amount', 15, 2)->default(0); // Monto del impuesto
            $table->decimal('taxable_amount', 15, 2)->default(0); // Monto gravable
            $table->string('tax_name')->nullable(); // Nombre del impuesto
            $table->string('tax_code')->nullable(); // Código del impuesto
            $table->timestamps();

            $table->foreign('voucher_id')->references('id')->on('vouchers')->onDelete('cascade'); // Relación con vouchers
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tax_totals');
    }
};
