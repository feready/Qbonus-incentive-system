<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('bonus_submissions', function (Blueprint $table) {
            $table->foreignId('master_file_version_id')
                ->nullable()
                ->constrained('master_file_versions')
                ->restrictOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('bonus_submissions', function (Blueprint $table) {
            $table->dropForeign(['master_file_version_id']);
            $table->dropColumn('master_file_version_id');
        });
    }
};
