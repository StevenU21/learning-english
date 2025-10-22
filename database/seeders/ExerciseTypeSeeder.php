<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\ExerciseType;

class ExerciseTypeSeeder extends Seeder
{
    /**
     * Run the database seeds for exercise types.
     */
    public function run(): void
    {
        ExerciseType::insert([
            ['name' => 'Opción múltiple', 'description' => 'Ejercicios de opción múltiple'],
            ['name' => 'Completar espacios', 'description' => 'Ejercicios para completar espacios en blanco'],
            ['name' => 'Verdadero o falso', 'description' => 'Ejercicios para determinar si las afirmaciones son verdaderas o falsas'],
            ['name' => 'Relacionar columnas', 'description' => 'Ejercicios para relacionar elementos de dos listas'],
            ['name' => 'Ordenar elementos', 'description' => 'Ejercicios para ordenar elementos en el orden correcto'],
            ['name' => 'Emparejar definiciones', 'description' => 'Ejercicios para emparejar conceptos con sus definiciones'],
            ['name' => 'Completar diálogo', 'description' => 'Ejercicios para completar diálogos con frases faltantes'],
            ['name' => 'Elige lo que escuchas', 'description' => 'Sube un audio y elige entre 2 y 4 opciones la correcta.'],
            ['name' => 'Escucha y responde', 'description' => 'Compara dos audios y responde si son iguales o distintos.'],
            ['name' => 'Escucha y escribe', 'description' => 'Escucha un audio y escribe lo que escuchas.'],
            ['name' => 'Traduce la oración', 'description' => 'Traduce una oración dada al idioma objetivo.'],
            ['name' => 'Di la frase', 'description' => 'El usuario debe decir una frase y el sistema la evalúa usando IA.'],
        ]);
    }
}
