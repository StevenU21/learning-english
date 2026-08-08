<?php

namespace Database\Seeders;

use App\Models\Level;
use Illuminate\Database\Seeder;

class LevelSeeder extends Seeder
{
    /**
     * Run the database seeds for levels.
     */
    public function run(): void
    {
        collect([
            ['name' => 'Básico', 'description' => 'Nivel inicial para comenzar el aprendizaje desde cero.'],
            ['name' => 'Intermedio', 'description' => 'Nivel para reforzar y ampliar conocimientos previos.'],
            ['name' => 'Avanzado', 'description' => 'Nivel para dominar y profundizar en los temas más complejos.'],
        ])->each(fn ($data) => Level::create($data));
    }
}
