<?php

namespace Database\Seeders;

use App\Models\Exercise;
use App\Models\ExerciseType;
use App\Models\Lesson;
use App\Models\Level;
use App\Models\Profile;
use App\Models\Unit;
use App\Models\User;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call(RolesAndPermissionsSeeder::class);

        $adminUser = User::factory()->create([
            'first_name' => 'Admin',
            'last_name' => 'User',
            'email' => 'admin@example.com',
            'password' => bcrypt('password')
        ]);
        Profile::factory()->create([
            'user_id' => $adminUser->id
        ]);
        $adminUser->assignRole('admin');

        ExerciseType::insert([
            ['name' => 'Opción múltiple', 'description' => 'Ejercicios de opción múltiple'],
            ['name' => 'Completar espacios', 'description' => 'Ejercicios para completar espacios en blanco'],
            ['name' => 'Verdadero o falso', 'description' => 'Ejercicios para determinar si las afirmaciones son verdaderas o falsas'],
            ['name' => 'Relacionar columnas', 'description' => 'Ejercicios para relacionar elementos de dos listas'],
            ['name' => 'Ordenar elementos', 'description' => 'Ejercicios para ordenar elementos en el orden correcto'],
            ['name' => 'Emparejar definiciones', 'description' => 'Ejercicios para emparejar conceptos con sus definiciones'],
            ['name' => 'Completar diálogo', 'description' => 'Ejercicios para completar diálogos con frases faltantes']
        ]);

        Level::insert([
            ['name' => 'Básico', 'description' => 'Nivel inicial para comenzar el aprendizaje desde cero.'],
            ['name' => 'Intermedio', 'description' => 'Nivel para reforzar y ampliar conocimientos previos.'],
            ['name' => 'Avanzado', 'description' => 'Nivel para dominar y profundizar en los temas más complejos.'],
        ]);

        $unit = Unit::create([
            'name' => 'Números y operaciones básicas',
            'description' => 'Que el estudiante aprenda a reconocer y operar con números naturales y operaciones aritméticas simples.',
            'expected_time' => 60,
            'level_id' => 1
        ]);

        $lesson = Lesson::create([
            'unit_id' => $unit->id,
            'name' => 'Contar del 1 al 20',
            'description' => 'Aprende a contar los números del 1 al 20.'
        ]);

        $multipleChoiceType = ExerciseType::where('name', 'Opción múltiple')->first();
        $fillBlankType = ExerciseType::where('name', 'Completar espacios')->first();

        Exercise::create([
            'lesson_id' => $lesson->id,
            'exercise_type_id' => $multipleChoiceType->id,
            'prompt' => '¿Cuál de los siguientes números viene después del 14?',
            'options' => [12, 13, 15, 16],
            'solution' => ['15']
        ]);

        Exercise::create([
            'lesson_id' => $lesson->id,
            'exercise_type_id' => $fillBlankType->id,
            'prompt' => 'Escribe el número que viene antes del 8.',
            'options' => [],
            'solution' => ['7']
        ]);
    }
}
