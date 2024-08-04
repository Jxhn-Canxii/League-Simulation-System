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
        Schema::create('seasons', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->integer('league_id');
            $table->integer('type');
            $table->integer('match_type');
            $table->integer('is_conference')->default(0); // so that the league can play in inter conference style
            $table->integer('status');
            $table->unsignedBigInteger('finals_winner_id')->nullable();
            $table->string('finals_winner_name')->nullable();
            $table->integer('finals_winner_score')->nullable();
            $table->unsignedBigInteger('finals_loser_id')->nullable();
            $table->string('finals_loser_name')->nullable();
            $table->integer('finals_loser_score')->nullable();
            $table->unsignedBigInteger('champion_id')->nullable();
            $table->string('champion_name')->nullable();
            $table->unsignedBigInteger('weakest_id')->nullable();
            $table->string('weakest_name')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('seasons');
    }
};
