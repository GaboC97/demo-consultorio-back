<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('conversations', function (Blueprint $table) {
            $table->id();

            // direct = 1 a 1 | group = grupo
            $table->enum('type', ['direct', 'group'])->default('direct');

            // nombre del grupo (en direct queda null)
            $table->string('title')->nullable();

            // para ordenar el inbox fácil y rápido
            $table->timestamp('last_message_at')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('conversations');
    }
};
