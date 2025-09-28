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
        Schema::create('exercises', function (Blueprint $table) {
            $table->increments('id');
            $table->string('prompt');
            $table->json('options')->nullable();
            $table->json('solution');
            $table->string('explanation')->nullable();

            $table->integer('exercise_type_id')->unsigned();
            $table->foreign('exercise_type_id')->references('id')->on('exercise_types')->onDelete('cascade')->onUpdate('cascade');

            $table->integer('lesson_id')->unsigned();
            $table->foreign('lesson_id')->references('id')->on('lessons')->onDelete('cascade')->onUpdate('cascade');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('exercises');
    }
};
