<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('user_profiles', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('user_id')->unique()->constrained()->cascadeOnDelete();
            $table->unsignedTinyInteger('age');
            $table->enum('gender', ['male', 'female']);
            $table->decimal('height', 5, 2);
            $table->decimal('weight', 5, 2);
            $table->enum('goal', ['lose', 'gain', 'lifestyle']);
            $table->decimal('target_weight', 5, 2)->nullable();
            $table->string('profile_image')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('user_profiles');
    }
};
