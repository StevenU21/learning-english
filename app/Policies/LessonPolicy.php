<?php

namespace App\Policies;

use App\Models\Lesson;
use App\Models\User;
use App\Traits\HasPermissionCheck;
use Illuminate\Auth\Access\HandlesAuthorization;

class LessonPolicy
{
    use HandlesAuthorization, HasPermissionCheck;

    public function viewAny(User $user): bool
    {
        return $this->checkPermission($user, 'read lessons');
    }

    public function view(User $user, Lesson $lesson): bool
    {
        return $this->checkPermission($user, 'read lessons');
    }

    public function create(User $user): bool
    {
        return $this->checkPermission($user, 'create lessons');
    }

    public function update(User $user, Lesson $lesson): bool
    {
        return $this->checkPermission($user, 'update lessons');
    }

    public function destroy(User $user, Lesson $lesson): bool
    {
        return $this->checkPermission($user, 'destroy lessons');
    }
}
