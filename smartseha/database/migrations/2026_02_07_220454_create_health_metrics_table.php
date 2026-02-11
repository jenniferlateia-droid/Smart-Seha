<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('health_metrics', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->decimal('weight', 5, 2);
            $table->unsignedSmallInteger('systolic')->nullable();
            $table->unsignedSmallInteger('diastolic')->nullable();
            $table->decimal('blood_sugar', 6, 2)->nullable();
            $table->date('recorded_date');
            $table->timestamps();

            $table->unique(['user_id', 'recorded_date']);
            $table->index(['user_id', 'recorded_date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('health_metrics');
    }
};
