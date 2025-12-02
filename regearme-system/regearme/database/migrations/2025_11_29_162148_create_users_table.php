<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('user_id')->unsigned()->unique();

            $table->bigInteger('guild_id')->unsigned()->nullable();

            $table->string('in_game_id')->unique();
            $table->string('in_game_name')->unique();
            $table->string('password');

            $table->enum('role', ['admin', 'officer', 'user'])->default('user');
            $table->enum('verification_status', ['pending', 'verified', 'rejected'])->default('pending');

            $table->timestamp('verified_at')->nullable();
            $table->rememberToken();
            $table->timestamps();

            $table->foreign('guild_id')->references('guild_id')->on('guilds')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('users');
    }
};
