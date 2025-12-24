<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
  public function up(): void
  {
    Schema::create('message_attachments', function (Blueprint $table) {
      $table->id();
      $table->foreignId('message_id')->constrained('messages')->cascadeOnDelete();

      // storage
      $table->string('disk')->default('public');            // por si mañana usás s3
      $table->string('file_path');                          // ej: chats/1/uuid.pdf
      $table->string('original_name');                      // nombre real
      $table->string('mime_type')->nullable();
      $table->unsignedBigInteger('size')->default(0);

      // extras útiles
      $table->string('sha1', 40)->nullable()->index();      // dedup opcional
      $table->timestamps();
    });
  }

  public function down(): void
  {
    Schema::dropIfExists('message_attachments');
  }
};
