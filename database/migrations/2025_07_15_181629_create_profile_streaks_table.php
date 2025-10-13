<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('profile_streaks', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('profile_id');
            $table->date('activity_date');
            $table->integer('minutes')->default(0); // minutos estudiados ese día
            $table->timestamps();

            $table->foreign('profile_id')->references('id')->on('profiles')->onDelete('cascade');
            $table->unique(['profile_id', 'activity_date']); // Un registro por día por perfil
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('profile_streaks');
    }
};
