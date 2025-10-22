<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Unit;
use App\Models\Lesson;
use App\Models\Level;

class ContentSeeder extends Seeder
{
    /**
     * Run the database seeds for units, lessons and exercise samples.
     */
    public function run(): void
    {
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

        // Aquí podrías crear ejercicios de ejemplo si lo deseas.
    }
}
