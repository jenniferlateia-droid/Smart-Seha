<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('health_metrics', function (Blueprint $table): void {
            $table->unsignedInteger('steps')->nullable()->after('blood_sugar');
            $table->decimal('water_intake_liters', 4, 2)->nullable()->after('steps');
            $table->decimal('sleep_hours', 4, 2)->nullable()->after('water_intake_liters');
            $table->unsignedSmallInteger('exercise_minutes')->nullable()->after('sleep_hours');
            $table->unsignedTinyInteger('mood_score')->nullable()->after('exercise_minutes');
        });
    }

    public function down(): void
    {
        Schema::table('health_metrics', function (Blueprint $table): void {
            $table->dropColumn([
                'steps',
                'water_intake_liters',
                'sleep_hours',
                'exercise_minutes',
                'mood_score',
            ]);
        });
    }
};
