<?php

namespace App\Policies;

use App\Models\Unit;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;
use App\Traits\HasPermissionCheck;

class UnitPolicy
{
    use HandlesAuthorization, HasPermissionCheck;

    public function viewAny(User $user): bool
    {
        return $this->checkPermission($user, 'read units');
    }

    public function view(User $user, Unit $unit): bool
    {
        return $this->checkPermission($user, 'read units');
    }

    public function create(User $user): bool
    {
        return $this->checkPermission($user, 'create units');
    }

    public function update(User $user, Unit $unit): bool
    {
        return $this->checkPermission($user, 'update units');
    }

    public function destroy(User $user, Unit $unit): bool
    {
        return $this->checkPermission($user, 'destroy units');
    }
}
