<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('bonus_submissions', function (Blueprint $table) {
            $table->id();

            // Pengguna yang meng-upload submission
            $table->foreignId('user_id')
                ->constrained('users')
                ->cascadeOnDelete();

            // Informasi file
            $table->string('original_filename');
            $table->string('file_path', 500);

            // Status validasi dan kalkulasi
            $table->string('validation_status', 20)->default('PENDING');
            $table->string('calculation_status', 20)->default('PENDING');

            // Ringkasan hasil
            $table->unsignedInteger('total_rows')->default(0);
            $table->unsignedInteger('match_count')->default(0);
            $table->unsignedInteger('difference_count')->default(0);
            $table->unsignedInteger('error_count')->default(0);

            // Detail hasil validasi dan kalkulasi
            $table->json('validation_summary')->nullable();
            $table->json('calculation_summary')->nullable();
            $table->json('results')->nullable();

            // Keputusan submission
            $table->string('decision_status', 20)->default('PENDING');

            // Alasan hanya diisi jika Appeal
            $table->text('appeal_reason')->nullable();

            // Pengguna dan waktu pengambilan keputusan
            $table->foreignId('decided_by')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->timestamp('decided_at')->nullable();

            $table->timestamps();

            $table->index(['user_id', 'decision_status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bonus_submissions');
    }
};
