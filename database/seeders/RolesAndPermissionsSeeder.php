<?php

namespace Database\Seeders;

use App\Classes\PermissionManager;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RolesAndPermissionsSeeder extends Seeder
{
    const PERMISSIONS = [
        'users' => [],
        'profiles' => ['read', 'update'],
        'roles' => ['read'],
        'permissions' => ['read'],
        'levels' => [],
        'units' => [],
        'resources' => [],
        'lessons' => [],
        'exercises' => [],
        'exercise_types' => ['read'],
    ];

    const SPECIAL_PERMISSIONS = [
        'permissions' => ['assign permissions', 'revoke permissions'],
        'resources' => ['download resources'],
    ];

    const ROLES = [
        'admin' => '*',
        'student' => [
            'profiles' => ['read', 'update'],
            'levels' => ['read'],
            'units' => ['read'],
            'resources' => ['read', 'download resources'],
            'lessons' => ['read'],
            'exercises' => ['read'],
            'exercise_types' => ['read'],
        ],
    ];

    /**
     * Run the database seeds.
     */
    public function run()
    {
        $manager = new PermissionManager(self::PERMISSIONS, self::SPECIAL_PERMISSIONS);
        $manager->withRoles(self::ROLES)->sync();
    }
}
