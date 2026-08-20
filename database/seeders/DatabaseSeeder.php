<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Roles, permissions and admin user
        $this->call(RolesAndPermissionsSeeder::class);

        $adminUser = User::firstOrCreate(
            ['email' => 'admin@example.com'],
            [
                'first_name' => 'Admin',
                'last_name' => 'User',
                'password' => Hash::make('password'),
                'email_verified_at' => now(),
            ]
        );

        $adminUser->profile()->firstOrCreate([]);

        if (! $adminUser->hasRole('admin')) {
            $adminUser->assignRole('admin');
        }

        // Core content seeders
        $this->call([
            ExerciseTypeSeeder::class,
            LevelSeeder::class,
            ContentSeeder::class,
            ExerciseSeeder::class,
        ]);
    }
}
