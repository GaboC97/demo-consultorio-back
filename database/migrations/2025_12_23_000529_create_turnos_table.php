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
Schema::create('turnos', function (Blueprint $col) {
    $col->id();
    $col->foreignId('paciente_id')->constrained();
    $col->foreignId('medico_emisor_id')->constrained('users'); // Quién deriva
    $col->foreignId('medico_receptor_id')->nullable()->constrained('users'); // Para quién es
    $col->date('fecha')->nullable();
    $col->time('hora')->nullable();
    $col->text('motivo');
    $col->boolean('es_derivacion')->default(false);
    $col->enum('estado', ['pendiente', 'atendido', 'cancelado'])->default('pendiente');
    $col->timestamps();
});
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('turnos');
    }
};
