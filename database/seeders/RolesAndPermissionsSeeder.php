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

    /**
     * Run the database seeds.
     */
    public function run()
    {
        $manager = new PermissionManager(self::PERMISSIONS, self::SPECIAL_PERMISSIONS);
        $allPermissions = $manager->get();

        $this->createPermissions($allPermissions);

        $this->assignPermissionsToRoles();
    }

    protected function createPermissions($permissions): void
    {
        foreach ($permissions as $perms) {
            foreach ($perms as $perm) {
                Permission::firstOrCreate(['name' => $perm]);
            }
        }
    }

    protected function filterPermissions($permission): PermissionManager
    {
        $permissions = self::PERMISSIONS[$permission] ?? [];
        $specialPermissions = self::SPECIAL_PERMISSIONS[$permission] ?? [];

        return new PermissionManager([$permission => $permissions], [$permission => $specialPermissions]);
    }

    protected function assignPermissionsToRoles(): void
    {
        $adminRole = Role::firstOrCreate(['name' => 'admin']);
        $studentRole = Role::firstOrCreate(['name' => 'student']);

        $adminRole->givePermissionTo(Permission::all());

        $studentPermissions = array_merge(
            $this->filterPermissions('profiles')->get(),
            $this->filterPermissions('levels')->only(['read levels'])->get(),
            $this->filterPermissions('units')->only(['read units'])->get(),
            $this->filterPermissions('resources')->only(['read resources', 'download resources'])->get(),
            $this->filterPermissions('lessons')->only(['read lessons'])->get(),
            $this->filterPermissions('exercises')->only(['read exercises'])->get(),
            $this->filterPermissions('exercise_types')->only(['read exercise_types'])->get()
        );

        $studentRole->givePermissionTo($studentPermissions);
    }
}
