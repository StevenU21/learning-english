<?php

namespace App\Policies;

use App\Models\ExerciseType;
use App\Models\User;
use App\Traits\HasPermissionCheck;
use Illuminate\Auth\Access\HandlesAuthorization;

class ExerciseTypePolicy
{
    use HandlesAuthorization, HasPermissionCheck;

    public function viewAny(User $user): bool
    {
        return $this->checkPermission($user, 'read exercise_types');
    }

    public function view(User $user, ExerciseType $exerciseType): bool
    {
        return $this->checkPermission($user, 'read exercise_types');
    }
}
