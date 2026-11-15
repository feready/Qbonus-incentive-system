<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('master_file_versions', function (Blueprint $table) {
            $table->id();

            $table->unsignedInteger('version_number')->unique();

            $table->string('original_filename');
            $table->string('file_path');

            $table->unsignedBigInteger('file_size')->default(0);
            $table->char('file_sha256', 64)->nullable();

            $table->foreignId('uploaded_by')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->string('validation_status', 20)
                ->default('PENDING');

            $table->json('validation_summary')->nullable();

            $table->string('status', 20)
                ->default('PENDING');

            $table->boolean('is_active')->default(false);

            $table->foreignId('activated_by')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->timestamp('activated_at')->nullable();

            $table->text('notes')->nullable();

            $table->timestamps();

            $table->index(
                ['is_active', 'status'],
                'mfv_active_status_idx'
            );
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('master_file_versions');
    }
};
