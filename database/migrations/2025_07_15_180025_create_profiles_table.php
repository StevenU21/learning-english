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
        Schema::create('profiles', function (Blueprint $table) {
            $table->increments('id');
            $table->string('avatar')->nullable();
            $table->string('nickname')->nullable()->unique();
            $table->date('birthdate')->nullable();
            $table->integer('daily_goal_minutes')->default(5);
            $table->integer('total_minutes')->default(0);
            // $table->integer('streak_days')->default(0); // Eliminado: la racha se maneja en profile_streaks
            $table->enum('gender', ['male', 'female'])->nullable();

            // Foreign key to users table
            $table->integer('user_id')->unsigned();
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade')->onUpdate('cascade');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('profiles');
    }
};
