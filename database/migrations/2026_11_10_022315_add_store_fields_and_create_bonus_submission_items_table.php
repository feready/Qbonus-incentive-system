<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('bonus_submissions', function (Blueprint $table) {
            $table->string('store_code', 50)->nullable();
            $table->string('store_name', 255)->nullable();
        });

        Schema::create('bonus_submission_items', function (Blueprint $table) {
            $table->id();

            $table->foreignId('bonus_submission_id')
                ->constrained('bonus_submissions')
                ->cascadeOnDelete();

            $table->unsignedInteger('excel_row');
            $table->string('employee_id', 100)->nullable();
            $table->string('employee_name', 255)->nullable();
            $table->string('employee_code', 50)->nullable();

            $table->json('source_data')->nullable();
            $table->json('calculation_data')->nullable();

            $table->decimal('excel_final', 20, 6)->nullable();
            $table->decimal('qbonus_final', 20, 6)->nullable();
            $table->decimal('difference', 20, 6)->nullable();

            $table->string('calculation_status', 20)->default('ERROR');

            $table->string('decision_status', 20)->default('PENDING');
            $table->text('appeal_reason')->nullable();

            $table->foreignId('decided_by')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->timestamp('decided_at')->nullable();

            $table->timestamps();

            // Gunakan nama index pendek agar kompatibel dengan MySQL.
            $table->unique(
                ['bonus_submission_id', 'excel_row'],
                'bsi_submission_row_unique'
            );

            $table->index(
                ['bonus_submission_id', 'calculation_status'],
                'bsi_submission_calc_idx'
            );

            $table->index(
                'decision_status',
                'bsi_decision_status_idx'
            );
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bonus_submission_items');

        Schema::table('bonus_submissions', function (Blueprint $table) {
            $table->dropColumn(['store_code', 'store_name']);
        });
    }
};
