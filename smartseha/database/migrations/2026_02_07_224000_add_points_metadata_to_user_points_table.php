<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('user_points', function (Blueprint $table): void {
            $table->string('action_key')->nullable()->after('activity')->index();
            $table->string('unique_key')->nullable()->after('action_key');
            $table->json('meta')->nullable()->after('unique_key');
            $table->unique(['user_id', 'unique_key']);
        });
    }

    public function down(): void
    {
        Schema::table('user_points', function (Blueprint $table): void {
            $table->dropUnique('user_points_user_id_unique_key_unique');
            $table->dropColumn(['action_key', 'unique_key', 'meta']);
        });
    }
};
