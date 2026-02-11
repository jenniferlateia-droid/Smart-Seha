<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('food_analyses', function (Blueprint $table): void {
            $table->string('status')->default('queued')->after('image_path');
            $table->string('model_used')->nullable()->after('status');
            $table->text('error_message')->nullable()->after('allergens');
            $table->json('analysis_payload')->nullable()->after('error_message');
        });
    }

    public function down(): void
    {
        Schema::table('food_analyses', function (Blueprint $table): void {
            $table->dropColumn(['status', 'model_used', 'error_message', 'analysis_payload']);
        });
    }
};
