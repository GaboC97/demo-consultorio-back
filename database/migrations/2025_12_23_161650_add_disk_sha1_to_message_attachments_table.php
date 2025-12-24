<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('message_attachments', function (Blueprint $table) {
            if (!Schema::hasColumn('message_attachments', 'disk')) {
                $table->string('disk', 50)->default('public')->after('message_id');
            }
            if (!Schema::hasColumn('message_attachments', 'sha1')) {
                $table->string('sha1', 40)->nullable()->after('size');
            }
        });
    }

    public function down(): void
    {
        Schema::table('message_attachments', function (Blueprint $table) {
            if (Schema::hasColumn('message_attachments', 'disk')) $table->dropColumn('disk');
            if (Schema::hasColumn('message_attachments', 'sha1')) $table->dropColumn('sha1');
        });
    }
};
