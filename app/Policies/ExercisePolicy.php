<?php

namespace App\Policies;

use App\Models\Exercise;
use App\Models\User;
use App\Traits\HasPermissionCheck;
use Illuminate\Auth\Access\HandlesAuthorization;

class ExercisePolicy
{
    use HandlesAuthorization, HasPermissionCheck;

    public function viewAny(User $user): bool
    {
        return $this->checkPermission($user, 'read exercises');
    }

    public function view(User $user, Exercise $exercise): bool
    {
        return $this->checkPermission($user, 'read exercises');
    }

    public function create(User $user): bool
    {
        return $this->checkPermission($user, 'create exercises');
    }

    public function update(User $user, Exercise $exercise): bool
    {
        return $this->checkPermission($user, 'update exercises');
    }

    public function destroy(User $user, Exercise $exercise): bool
    {
        return $this->checkPermission($user, 'destroy exercises');
    }
}
