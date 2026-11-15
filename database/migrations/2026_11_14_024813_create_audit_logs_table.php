<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('audit_logs', function (Blueprint $table) {
            $table->id();

            $table->foreignId('user_id')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            // Identitas disimpan agar riwayat tetap terbaca
            // meskipun akun pengguna nantinya dihapus.
            $table->string('user_name')->nullable();
            $table->string('user_email')->nullable();

            $table->string('event', 60);
            $table->text('description')->nullable();

            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->text('url')->nullable();
            $table->string('method', 10)->nullable();

            $table->json('metadata')->nullable();

            $table->timestamps();

            $table->index('event', 'audit_event_idx');
            $table->index(
                ['user_id', 'created_at'],
                'audit_user_created_idx'
            );
            $table->index('created_at', 'audit_created_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('audit_logs');
    }
};
