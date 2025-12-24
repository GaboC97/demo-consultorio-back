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
        Schema::table('turnos', function (Blueprint $table) {
            // Esta es la clave: vinculamos a la tabla users
            $table->foreignId('medico_receptor_id')
                ->nullable() // Es nullable porque un turno normal no tiene receptor
                ->after('user_id') // Para que quede ordenado en la BD
                ->constrained('users') // Le dice a Laravel que apunte a la tabla de médicos
                ->onDelete('set null');

            $table->boolean('es_derivacion')->default(false)->after('motivo');
        });
    }

    public function down(): void
    {
        Schema::table('turnos', function (Blueprint $table) {
            $table->dropForeign(['medico_receptor_id']);
            $table->dropColumn(['medico_receptor_id', 'es_derivacion']);
        });
    }
};
