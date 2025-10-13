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

        // Usar Eloquent para disparar eventos de modelo y generar el slug automáticamente
        collect([
            ['name' => 'Básico', 'description' => 'Nivel inicial para comenzar el aprendizaje desde cero.'],
            ['name' => 'Intermedio', 'description' => 'Nivel para reforzar y ampliar conocimientos previos.'],
            ['name' => 'Avanzado', 'description' => 'Nivel para dominar y profundizar en los temas más complejos.'],
        ])->each(fn($data) => Level::create($data));
        // Unidad y lecciones enfocadas al aprendizaje básico de inglés
        $unit = Unit::create([
            'name' => 'Introducción al Inglés',
            'description' => 'Fundamentos iniciales para comenzar a comunicarse en inglés: saludos, presentaciones, números y colores.',
            'level_id' => Level::where('name', 'Básico')->value('id') ?? 1,
        ]);

        $lessonGreetings = Lesson::create([
            'unit_id' => $unit->id,
            'name' => 'Saludos y Presentaciones',
            'description' => 'Aprende saludos básicos y cómo presentarte.',
            'duration' => 2,
        ]);

        $lessonNumbersColors = Lesson::create([
            'unit_id' => $unit->id,
            'name' => 'Números y Colores',
            'description' => 'Aprende los números básicos y los colores más comunes.',
            'duration' => 2,
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
         * Lección 1: Saludos y Presentaciones (8 ejercicios con propósito real)
         */
        Exercise::create([
            'lesson_id' => $lessonGreetings->id,
            'exercise_type_id' => $typeMultiple->id,
            'prompt' => 'Estás en clase a las 8:00 a.m. ¿Cuál es el saludo más adecuado?',
            'options' => ['Good morning', 'Good night', 'Goodbye', 'See you!'],
            'solution' => ['Good morning']
        ]);

        Exercise::create([
            'lesson_id' => $lessonGreetings->id,
            'exercise_type_id' => $typeFill->id,
            'prompt' => 'Completa: Hi, I ___ Carlos.',
            'options' => [],
            'solution' => ['am']
        ]);

        Exercise::create([
            'lesson_id' => $lessonGreetings->id,
            'exercise_type_id' => $typeTrueFalse->id,
            'prompt' => '"Nice to meet you" se usa cuando conoces a alguien por primera vez.',
            'options' => [],
            'solution' => ['True']
        ]);

        Exercise::create([
            'lesson_id' => $lessonGreetings->id,
            'exercise_type_id' => $typeMatchCols->id,
            'prompt' => 'Relaciona la pregunta con la respuesta apropiada.',
            'options' => [
                ['What\'s your name?', "I\'m Tom."],
                ['How are you?', "I\'m fine, thanks."],
                ['Where are you from?', "I\'m from Peru."],
            ],
            'solution' => [
                ['What\'s your name?', "I\'m Tom."],
                ['How are you?', "I\'m fine, thanks."],
                ['Where are you from?', "I\'m from Peru."],
            ]
        ]);

        Exercise::create([
            'lesson_id' => $lessonGreetings->id,
            'exercise_type_id' => $typeOrder->id,
            'prompt' => 'Ordena las palabras para formar: "We are friends"',
            'options' => ['friends', 'are', 'We'],
            'solution' => ['We', 'are', 'friends']
        ]);

        Exercise::create([
            'lesson_id' => $lessonGreetings->id,
            'exercise_type_id' => $typePairDefs->id,
            'prompt' => 'Empareja cada concepto con su definición.',
            'options' => [
                ['concepto' => 'Good morning', 'definicion' => 'Greeting used before noon'],
                ['concepto' => 'Goodbye', 'definicion' => 'A way to say farewell'],
                ['concepto' => 'Name', 'definicion' => 'What people call you'],
            ],
            'solution' => [
                ['concepto' => 'Good morning', 'definicion' => 'Greeting used before noon'],
                ['concepto' => 'Goodbye', 'definicion' => 'A way to say farewell'],
                ['concepto' => 'Name', 'definicion' => 'What people call you'],
            ]
        ]);

        Exercise::create([
            'lesson_id' => $lessonGreetings->id,
            'exercise_type_id' => $typeDialogue->id,
            'prompt' => 'Completa el diálogo: A: "Hi, I\'m Lisa. _____ your name?" B: "I\'m Mark."',
            'options' => [
                'A: Hi, I\'m Lisa. What is your name?',
                'A: Hi, I\'m Lisa. Where is your name?',
                'A: Hi, I\'m Lisa. How is your name?',
            ],
            'solution' => ['A: Hi, I\'m Lisa. What is your name?']
        ]);

        Exercise::create([
            'lesson_id' => $lessonGreetings->id,
            'exercise_type_id' => $typeMultiple->id,
            'prompt' => 'Responde de forma natural: "Thank you!"',
            'options' => [
                'You\'re welcome.',
                'Please.',
                'Sorry.',
                'Good night.'
            ],
            'solution' => ['You\'re welcome.']
        ]);

        /*
         * Lección 2: Números y Colores (8 ejercicios con propósito real)
         */
        Exercise::create([
            'lesson_id' => $lessonNumbersColors->id,
            'exercise_type_id' => $typeMultiple->id,
            'prompt' => 'Semáforo: ¿Qué color significa "avanzar/GO"?',
            'options' => ['Green', 'Red', 'Yellow', 'Blue'],
            'solution' => ['Green']
        ]);

        Exercise::create([
            'lesson_id' => $lessonNumbersColors->id,
            'exercise_type_id' => $typeFill->id,
            'prompt' => 'Completa: The banana is ____.',
            'options' => [],
            'solution' => ['yellow']
        ]);

        Exercise::create([
            'lesson_id' => $lessonNumbersColors->id,
            'exercise_type_id' => $typeTrueFalse->id,
            'prompt' => 'La palabra en inglés para el número 0 es "zero".',
            'options' => [],
            'solution' => ['True']
        ]);

        Exercise::create([
            'lesson_id' => $lessonNumbersColors->id,
            'exercise_type_id' => $typeOrder->id,
            'prompt' => 'Ordena para formar: "I have two blue pens"',
            'options' => ['two', 'pens', 'have', 'blue', 'I'],
            'solution' => ['I', 'have', 'two', 'blue', 'pens']
        ]);

        Exercise::create([
            'lesson_id' => $lessonNumbersColors->id,
            'exercise_type_id' => $typeMatchCols->id,
            'prompt' => 'Relaciona el número con su palabra.',
            'options' => [
                ['4', 'four'],
                ['7', 'seven'],
                ['9', 'nine'],
            ],
            'solution' => [
                ['4', 'four'],
                ['7', 'seven'],
                ['9', 'nine'],
            ]
        ]);

        Exercise::create([
            'lesson_id' => $lessonNumbersColors->id,
            'exercise_type_id' => $typePairDefs->id,
            'prompt' => 'Empareja el color con su definición.',
            'options' => [
                ['concepto' => 'Blue', 'definicion' => 'Color of the sky'],
                ['concepto' => 'Yellow', 'definicion' => 'Color of a banana'],
                ['concepto' => 'Black', 'definicion' => 'Color of the night'],
            ],
            'solution' => [
                ['concepto' => 'Blue', 'definicion' => 'Color of the sky'],
                ['concepto' => 'Yellow', 'definicion' => 'Color of a banana'],
                ['concepto' => 'Black', 'definicion' => 'Color of the night'],
            ]
        ]);

        Exercise::create([
            'lesson_id' => $lessonNumbersColors->id,
            'exercise_type_id' => $typeDialogue->id,
            'prompt' => 'Completa el diálogo: A: "How many apples do you have?" B: "I have _____."',
            'options' => ['three', 'red', 'Monday'],
            'solution' => ['three']
        ]);

        Exercise::create([
            'lesson_id' => $lessonNumbersColors->id,
            'exercise_type_id' => $typeMultiple->id,
            'prompt' => 'Compras 2 manzanas y luego 1 más. ¿Cuántas tienes?',
            'options' => ['2', '3', '4', '1'],
            'solution' => ['3']
        ]);
    }
}
