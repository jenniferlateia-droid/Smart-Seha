<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('recommendation_logs', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('category', 50)->nullable();
            $table->string('icon')->nullable();
            $table->string('title')->nullable();
            $table->text('description')->nullable();
            $table->timestamp('created_date')->useCurrent();
            $table->timestamps();

            $table->index(['user_id', 'created_date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('recommendation_logs');
    }
};
