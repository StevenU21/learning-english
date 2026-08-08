<?php

namespace Database\Seeders;

use App\Models\Exercise;
use App\Models\ExerciseType;
use App\Models\Lesson;
use Illuminate\Database\Seeder;

class ExerciseSeeder extends Seeder
{
    /**
     * Run the database seeds for exercises.
     */
    public function run(): void
    {
        // Lección 1: Saludos y Presentaciones
        $lessonGreetings = Lesson::where('name', 'Saludos y Presentaciones')->first();

        // Obtener tipos de ejercicio
        $typeMultiple = ExerciseType::where('name', 'Opción múltiple')->first();
        $typeFill = ExerciseType::where('name', 'Completar espacios')->first();
        $typeTrueFalse = ExerciseType::where('name', 'Verdadero o falso')->first();
        $typeMatchCols = ExerciseType::where('name', 'Relacionar columnas')->first();
        $typeOrder = ExerciseType::where('name', 'Ordenar elementos')->first();
        $typePairDefs = ExerciseType::where('name', 'Emparejar definiciones')->first();
        $typeDialogue = ExerciseType::where('name', 'Completar diálogo')->first();

        // Ejercicios Saludos y Presentaciones
        Exercise::create([
            'lesson_id' => $lessonGreetings->id,
            'exercise_type_id' => $typeMultiple->id,
            'prompt' => 'Estás en clase a las 8:00 a.m. ¿Cuál es el saludo más adecuado?',
            'options' => ['Good morning', 'Good night', 'Goodbye', 'See you!'],
            'solution' => ['Good morning'],
        ]);

        Exercise::create([
            'lesson_id' => $lessonGreetings->id,
            'exercise_type_id' => $typeFill->id,
            'prompt' => 'Completa: Hi, I ___ Carlos.',
            'options' => [],
            'solution' => ['am'],
        ]);

        Exercise::create([
            'lesson_id' => $lessonGreetings->id,
            'exercise_type_id' => $typeTrueFalse->id,
            'prompt' => '"Nice to meet you" se usa cuando conoces a alguien por primera vez.',
            'options' => [],
            'solution' => ['True'],
        ]);

        Exercise::create([
            'lesson_id' => $lessonGreetings->id,
            'exercise_type_id' => $typeMatchCols->id,
            'prompt' => 'Relaciona la pregunta con la respuesta apropiada.',
            'options' => [
                ['left' => "What's your name?", 'right' => "I'm Tom."],
                ['left' => 'How are you?', 'right' => "I'm fine, thanks."],
                ['left' => 'Where are you from?', 'right' => "I'm from Peru."],
            ],
            'solution' => [
                ['left' => "What's your name?", 'right' => "I'm Tom."],
                ['left' => 'How are you?', 'right' => "I'm fine, thanks."],
                ['left' => 'Where are you from?', 'right' => "I'm from Peru."],
            ],
        ]);

        Exercise::create([
            'lesson_id' => $lessonGreetings->id,
            'exercise_type_id' => $typeOrder->id,
            'prompt' => 'Ordena las palabras para formar: "We are friends"',
            'options' => ['friends', 'are', 'We'],
            'solution' => ['We', 'are', 'friends'],
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
            ],
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
            'solution' => ['A: Hi, I\'m Lisa. What is your name?'],
        ]);

        Exercise::create([
            'lesson_id' => $lessonGreetings->id,
            'exercise_type_id' => $typeMultiple->id,
            'prompt' => 'Responde de forma natural: "Thank you!"',
            'options' => [
                'You\'re welcome.',
                'Please.',
                'Sorry.',
                'Good night.',
            ],
            'solution' => ['You\'re welcome.'],
        ]);

        // Lección 2: Números y Colores
        $lessonNumbersColors = Lesson::where('name', 'Números y Colores')->first();

        Exercise::create([
            'lesson_id' => $lessonNumbersColors->id,
            'exercise_type_id' => $typeMultiple->id,
            'prompt' => 'Semáforo: ¿Qué color significa "avanzar/GO"?',
            'options' => ['Green', 'Red', 'Yellow', 'Blue'],
            'solution' => ['Green'],
        ]);

        Exercise::create([
            'lesson_id' => $lessonNumbersColors->id,
            'exercise_type_id' => $typeFill->id,
            'prompt' => 'Completa: The banana is ____.',
            'options' => [],
            'solution' => ['yellow'],
        ]);

        Exercise::create([
            'lesson_id' => $lessonNumbersColors->id,
            'exercise_type_id' => $typeTrueFalse->id,
            'prompt' => 'La palabra en inglés para el número 0 es "zero".',
            'options' => [],
            'solution' => ['True'],
        ]);

        Exercise::create([
            'lesson_id' => $lessonNumbersColors->id,
            'exercise_type_id' => $typeOrder->id,
            'prompt' => 'Ordena para formar: "I have two blue pens"',
            'options' => ['two', 'pens', 'have', 'blue', 'I'],
            'solution' => ['I', 'have', 'two', 'blue', 'pens'],
        ]);

        Exercise::create([
            'lesson_id' => $lessonNumbersColors->id,
            'exercise_type_id' => $typeMatchCols->id,
            'prompt' => 'Relaciona el número con su palabra.',
            'options' => [
                ['left' => '4', 'right' => 'four'],
                ['left' => '7', 'right' => 'seven'],
                ['left' => '9', 'right' => 'nine'],
            ],
            'solution' => [
                ['left' => '4', 'right' => 'four'],
                ['left' => '7', 'right' => 'seven'],
                ['left' => '9', 'right' => 'nine'],
            ],
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
            ],
        ]);

        Exercise::create([
            'lesson_id' => $lessonNumbersColors->id,
            'exercise_type_id' => $typeDialogue->id,
            'prompt' => 'Completa el diálogo: A: "How many apples do you have?" B: "I have _____".',
            'options' => ['three', 'red', 'Monday'],
            'solution' => ['three'],
        ]);

        Exercise::create([
            'lesson_id' => $lessonNumbersColors->id,
            'exercise_type_id' => $typeMultiple->id,
            'prompt' => 'Compras 2 manzanas y luego 1 más. ¿Cuántas tienes?',
            'options' => ['2', '3', '4', '1'],
            'solution' => ['3'],
        ]);

        // Ejercicios adicionales
        Exercise::create([
            'lesson_id' => $lessonGreetings->id,
            'exercise_type_id' => $typeTrueFalse->id,
            'prompt' => 'La frase "See you!" se usa para despedirse.',
            'options' => [],
            'solution' => ['True'],
        ]);

        Exercise::create([
            'lesson_id' => $lessonNumbersColors->id,
            'exercise_type_id' => $typeFill->id,
            'prompt' => 'Completa: The sky is _____.',
            'options' => [],
            'solution' => ['blue'],
        ]);

        Exercise::create([
            'lesson_id' => $lessonGreetings->id,
            'exercise_type_id' => $typeOrder->id,
            'prompt' => 'Ordena para formar: "My name is Carlos"',
            'options' => ['Carlos', 'is', 'name', 'My'],
            'solution' => ['My', 'name', 'is', 'Carlos'],
        ]);

        Exercise::create([
            'lesson_id' => $lessonNumbersColors->id,
            'exercise_type_id' => $typeMatchCols->id,
            'prompt' => 'Relaciona el color con su traducción en inglés.',
            'options' => [
                ['left' => 'Rojo', 'right' => 'Red'],
                ['left' => 'Verde', 'right' => 'Green'],
                ['left' => 'Azul', 'right' => 'Blue'],
            ],
            'solution' => [
                ['left' => 'Rojo', 'right' => 'Red'],
                ['left' => 'Verde', 'right' => 'Green'],
                ['left' => 'Azul', 'right' => 'Blue'],
            ],
        ]);
    }
}
