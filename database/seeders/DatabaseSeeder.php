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
            ['name' => 'Completar diálogo', 'description' => 'Ejercicios para completar diálogos con frases faltantes'],
            ['name' => 'Elige lo que escuchas', 'description' => 'Sube un audio y elige entre 2 y 4 opciones la correcta.'],
            ['name' => 'Escucha y responde', 'description' => 'Compara dos audios y responde si son iguales o distintos.']
        ]);

        Level::insert([
            ['name' => 'Básico', 'description' => 'Nivel inicial para comenzar el aprendizaje desde cero.'],
            ['name' => 'Intermedio', 'description' => 'Nivel para reforzar y ampliar conocimientos previos.'],
            ['name' => 'Avanzado', 'description' => 'Nivel para dominar y profundizar en los temas más complejos.'],
        ]);
        // Unidad y lecciones enfocadas al aprendizaje básico de inglés
        $unit = Unit::create([
            'name' => 'Introducción al Inglés',
            'description' => 'Fundamentos iniciales para comenzar a comunicarse en inglés: saludos, presentaciones, números y colores.',
            'expected_time' => 90,
            'level_id' => Level::where('name', 'Básico')->value('id') ?? 1,
        ]);

        $lessonGreetings = Lesson::create([
            'unit_id' => $unit->id,
            'name' => 'Saludos y Presentaciones',
            'description' => 'Aprende saludos básicos y cómo presentarte.'
        ]);

        $lessonNumbersColors = Lesson::create([
            'unit_id' => $unit->id,
            'name' => 'Números y Colores',
            'description' => 'Aprende los números básicos y los colores más comunes.'
        ]);

        // Obtener tipos de ejercicio
        $typeMultiple = ExerciseType::where('name', 'Opción múltiple')->first();
        $typeFill = ExerciseType::where('name', 'Completar espacios')->first();
        $typeTrueFalse = ExerciseType::where('name', 'Verdadero o falso')->first();
        $typeMatchCols = ExerciseType::where('name', 'Relacionar columnas')->first();
        $typeOrder = ExerciseType::where('name', 'Ordenar elementos')->first();
        $typePairDefs = ExerciseType::where('name', 'Emparejar definiciones')->first();
        $typeDialogue = ExerciseType::where('name', 'Completar diálogo')->first();

        /*
         * Lección 1: Saludos y Presentaciones (8 ejercicios variados)
         */
        Exercise::create([
            'lesson_id' => $lessonGreetings->id,
            'exercise_type_id' => $typeMultiple->id,
            'prompt' => 'Selecciona el saludo apropiado para la mañana:',
            'options' => ['Good morning', 'Good night', 'Goodbye', 'See you later'],
            'solution' => ['Good morning']
        ]);

        Exercise::create([
            'lesson_id' => $lessonGreetings->id,
            'exercise_type_id' => $typeFill->id,
            'prompt' => 'Completa: Hello, my name ___ John.',
            'options' => [],
            'solution' => ['is']
        ]);

        Exercise::create([
            'lesson_id' => $lessonGreetings->id,
            'exercise_type_id' => $typeTrueFalse->id,
            'prompt' => 'La frase "See you later" se usa para despedirse.',
            'options' => [], // será forzado a True/False por la lógica
            'solution' => ['True']
        ]);

        Exercise::create([
            'lesson_id' => $lessonGreetings->id,
            'exercise_type_id' => $typeMatchCols->id,
            'prompt' => 'Relaciona el saludo en inglés con su equivalente en español.',
            'options' => [
                ['Hello', 'Hola'],
                ['Goodbye', 'Adiós'],
                ['Thanks', 'Gracias'],
            ],
            'solution' => [
                ['Hello', 'Hola'],
                ['Goodbye', 'Adiós'],
                ['Thanks', 'Gracias'],
            ]
        ]);

        Exercise::create([
            'lesson_id' => $lessonGreetings->id,
            'exercise_type_id' => $typeOrder->id,
            'prompt' => 'Ordena las palabras para formar la oración: "My name is Ana"',
            'options' => ['name', 'is', 'My', 'Ana'],
            'solution' => ['My', 'name', 'is', 'Ana']
        ]);

        Exercise::create([
            'lesson_id' => $lessonGreetings->id,
            'exercise_type_id' => $typePairDefs->id,
            'prompt' => 'Empareja cada palabra con su definición.',
            'options' => [
                ['concepto' => 'Hello', 'definicion' => 'A greeting'],
                ['concepto' => 'Bye', 'definicion' => 'A way to say farewell'],
                ['concepto' => 'Name', 'definicion' => 'What you are called'],
            ],
            'solution' => [
                ['concepto' => 'Hello', 'definicion' => 'A greeting'],
                ['concepto' => 'Bye', 'definicion' => 'A way to say farewell'],
                ['concepto' => 'Name', 'definicion' => 'What you are called'],
            ]
        ]);

        Exercise::create([
            'lesson_id' => $lessonGreetings->id,
            'exercise_type_id' => $typeDialogue->id,
            'prompt' => 'Completa el diálogo: A: "Hi! ____ name is Mark." B: "Nice to meet you, Mark."',
            'options' => [
                'A: Hi! My name is Mark.',
                'A: Hi! Your name is Mark.',
                'A: Hi! His name is Mark.',
            ],
            'solution' => ['A: Hi! My name is Mark.']
        ]);

        Exercise::create([
            'lesson_id' => $lessonGreetings->id,
            'exercise_type_id' => $typeMultiple->id,
            'prompt' => 'Elige la forma correcta para responder: "What\'s your name?"',
            'options' => [
                'I am name Carlos.',
                'My name is Carlos.',
                'Name my is Carlos.',
                'Carlos is name my.'
            ],
            'solution' => ['My name is Carlos.']
        ]);

        /*
         * Lección 2: Números y Colores (8 ejercicios variados)
         */
        Exercise::create([
            'lesson_id' => $lessonNumbersColors->id,
            'exercise_type_id' => $typeMultiple->id,
            'prompt' => '¿Qué número viene después de 5?',
            'options' => ['4', '6', '8', '2'],
            'solution' => ['6']
        ]);

        Exercise::create([
            'lesson_id' => $lessonNumbersColors->id,
            'exercise_type_id' => $typeFill->id,
            'prompt' => 'Completa: The color of the sky is ____.',
            'options' => [],
            'solution' => ['blue']
        ]);

        Exercise::create([
            'lesson_id' => $lessonNumbersColors->id,
            'exercise_type_id' => $typeTrueFalse->id,
            'prompt' => 'La palabra en inglés para el número 10 es "ten".',
            'options' => [],
            'solution' => ['True']
        ]);

        Exercise::create([
            'lesson_id' => $lessonNumbersColors->id,
            'exercise_type_id' => $typeOrder->id,
            'prompt' => 'Ordena para formar: "There are three red apples"',
            'options' => ['three', 'There', 'red', 'are', 'apples'],
            'solution' => ['There', 'are', 'three', 'red', 'apples']
        ]);

        Exercise::create([
            'lesson_id' => $lessonNumbersColors->id,
            'exercise_type_id' => $typeMatchCols->id,
            'prompt' => 'Relaciona el número con su palabra.',
            'options' => [
                ['1', 'one'],
                ['2', 'two'],
                ['3', 'three'],
            ],
            'solution' => [
                ['1', 'one'],
                ['2', 'two'],
                ['3', 'three'],
            ]
        ]);

        Exercise::create([
            'lesson_id' => $lessonNumbersColors->id,
            'exercise_type_id' => $typePairDefs->id,
            'prompt' => 'Empareja el color con su definición.',
            'options' => [
                ['concepto' => 'Red', 'definicion' => 'Color of many apples'],
                ['concepto' => 'Blue', 'definicion' => 'Color of the clear sky'],
                ['concepto' => 'Green', 'definicion' => 'Color of grass'],
            ],
            'solution' => [
                ['concepto' => 'Red', 'definicion' => 'Color of many apples'],
                ['concepto' => 'Blue', 'definicion' => 'Color of the clear sky'],
                ['concepto' => 'Green', 'definicion' => 'Color of grass'],
            ]
        ]);

        Exercise::create([
            'lesson_id' => $lessonNumbersColors->id,
            'exercise_type_id' => $typeDialogue->id,
            'prompt' => 'Completa el diálogo: A: "What color is the sun?" B: "It is _____."',
            'options' => ['yellow', 'blue', 'black'],
            'solution' => ['yellow']
        ]);

        Exercise::create([
            'lesson_id' => $lessonNumbersColors->id,
            'exercise_type_id' => $typeMultiple->id,
            'prompt' => '¿Qué color resulta de mezclar rojo (red) y azul (blue)?',
            'options' => ['Purple', 'Green', 'Orange', 'Brown'],
            'solution' => ['Purple']
        ]);
    }
}
