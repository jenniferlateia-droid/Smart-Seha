<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('food_analyses', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('food_name')->nullable();
            $table->string('image_path')->nullable();
            $table->unsignedInteger('calories')->nullable();
            $table->decimal('protein', 5, 2)->nullable();
            $table->decimal('carbs', 5, 2)->nullable();
            $table->decimal('fat', 5, 2)->nullable();
            $table->json('minerals')->nullable();
            $table->json('allergens')->nullable();
            $table->timestamp('analyzed_date')->useCurrent();
            $table->timestamps();

            $table->index(['user_id', 'analyzed_date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('food_analyses');
    }
};
