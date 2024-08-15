<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::create('players', function (Blueprint $table) {
            $table->id(); // Primary key
            $table->string('name'); // Player name or other columns
            $table->unsignedBigInteger('team_id')->nullable();
            $table->integer('contract_years')->default(1);
            $table->timestamp('contract_expires_at')->nullable();
            $table->boolean('is_active')->default(true);
            $table->integer('age')->default(0);
            $table->integer('retirement_age')->default(0);
            $table->decimal('injury_prone_percentage', 5, 2)->default(0.00);
            $table->string('role')->default('bench'); // Default role is 'bench

            // Rating columns
            $table->decimal('shooting_rating', 5, 2)->default(0.00);
            $table->decimal('defense_rating', 5, 2)->default(0.00);
            $table->decimal('passing_rating', 5, 2)->default(0.00);
            $table->decimal('rebounding_rating', 5, 2)->default(0.00);
            $table->decimal('overall_rating', 5, 2)->default(0.00);

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down()
    {
        Schema::dropIfExists('players');
    }
};
