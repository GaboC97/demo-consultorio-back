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
    Schema::create('consultations', function (Blueprint $table) {
        $table->id();
        
        // Relaciones (Foreign Keys)
        $table->foreignId('patient_id')->constrained()->onDelete('cascade');
        $table->foreignId('user_id')->constrained()->onDelete('cascade'); // El Médico
        
        // El contenido de la consulta (Formato médico SOAP)
        $table->text('reason')->comment('Motivo de la consulta');
        $table->text('symptoms')->nullable();
        $table->text('physical_exam')->nullable();
        $table->text('diagnosis'); // Diagnóstico principal
        $table->text('treatment'); // Indicaciones médicas
        
        $table->timestamps();
    });
}
    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('consultations');
    }
};
