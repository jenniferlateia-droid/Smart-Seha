<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('user_points', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('activity');
            $table->unsignedInteger('points')->default(0);
            $table->date('recorded_date');
            $table->timestamps();

            $table->index(['user_id', 'recorded_date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('user_points');
    }
};
