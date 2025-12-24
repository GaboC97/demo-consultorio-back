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
    Schema::create('patients', function (Blueprint $table) {
        $table->id();
        $table->string('first_name');
        $table->string('last_name');
        $table->string('dni')->unique();
        $table->date('birth_date');
        $table->enum('gender', ['M', 'F', 'Otro'])->nullable();
        $table->enum('blood_type', ['A+', 'A-', 'B+', 'B-', 'AB+', 'AB-', 'O+', 'O-'])->nullable();
        $table->string('phone')->nullable();
        $table->string('email')->nullable();
        $table->text('allergies')->nullable(); // Para alertas rápidas de texto
        $table->timestamps();
        $table->softDeletes(); // Protege los datos médicos de borrados accidentales
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('patients');
    }
};
