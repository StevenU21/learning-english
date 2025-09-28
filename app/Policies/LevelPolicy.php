<?php

namespace App\Policies;

use App\Models\Level;
use App\Models\User;
use App\Traits\HasPermissionCheck;
use Illuminate\Auth\Access\HandlesAuthorization;

class LevelPolicy
{
    use HandlesAuthorization, HasPermissionCheck;

    public function viewAny(User $user): bool
    {
        return $this->checkPermission($user, 'read levels');
    }

    public function view(User $user, Level $level): bool
    {
        return $this->checkPermission($user, 'read levels');
    }

    public function create(User $user): bool
    {
        return $this->checkPermission($user, 'create levels');
    }

    public function update(User $user, Level $level): bool
    {
        return $this->checkPermission($user, 'update levels');
    }

    public function destroy(User $user, Level $level): bool
    {
        return $this->checkPermission($user, 'destroy levels');
    }
}
