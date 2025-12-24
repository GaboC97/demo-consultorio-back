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
    Schema::create('turno_adjuntos', function (Blueprint $table) {
        $table->id();
        // Relación con el turno
        $table->foreignId('turno_id')->constrained('turnos')->onDelete('cascade');
        
        // Datos del archivo
        $table->string('nombre_original'); // Ejemplo: 'radiografia_torax.png'
        $table->string('ruta');            // Ejemplo: 'adjuntos/turnos/abc123...png'
        $table->string('mime_type');       // Ejemplo: 'image/png' o 'application/pdf'
        $table->integer('size');           // Tamaño en bytes
        $table->timestamps();
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('turno_adjuntos');
    }
};
