<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('employees', function (Blueprint $table) {

            $table->id();

            $table->uuid('uuid')->unique();

            $table->string('nik',30)->unique();

            $table->string('name',150);

            $table->foreignId('division_id')
                ->constrained('divisions')
                ->cascadeOnUpdate()
                ->restrictOnDelete();

            $table->foreignId('position_id')
                ->constrained('positions')
                ->cascadeOnUpdate()
                ->restrictOnDelete();

            $table->foreignId('bank_id')
                ->nullable()
                ->constrained('banks')
                ->cascadeOnUpdate()
                ->nullOnDelete();

            $table->string('account_number',50)->nullable();

            $table->string('npwp',30)->nullable();

            $table->date('join_date')->nullable();

            $table->boolean('active')->default(true);

            $table->timestamps();

        });
    }

    public function down(): void
    {
        Schema::dropIfExists('employees');
    }
};