<?php

use Illuminate\Support\Facades\DB;
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
        Schema::create('vouchers', function (Blueprint $table) {
            $table->id('id'); // UUID como identificador único
            $table->unsignedBigInteger('user_id'); // 'user_id' debe ser un entero (relación con 'users')
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade'); // Relación con la tabla 'users'
            $table->string('invoice_id')->nullable(); // ID del comprobante
            $table->date('issue_date')->nullable(); // Fecha de emisión
            $table->time('issue_time')->nullable(); // Hora de emisión
            $table->string('currency', 10)->nullable(); // Código de moneda
            $table->string('issuer_name')->nullable(); // Nombre del emisor
            $table->string('issuer_document_type', 10)->nullable(); // Tipo de documento del emisor
            $table->string('issuer_document_number', 50)->nullable(); // Número de documento del emisor
            $table->string('receiver_name')->nullable(); // Nombre del receptor
            $table->string('receiver_document_number', 50)->nullable(); // Número de documento del receptor
            $table->decimal('total_amount', 15, 2)->default(0); // Monto total sin impuestos (nuevo campo)
            $table->decimal('payable_amount', 15, 2)->default(0); // Monto total a pagar
            $table->text('xml_content')->nullable(); // Contenido completo del XML
            $table->timestamps(); // created_at y updated_at

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('vouchers');
    }
};
