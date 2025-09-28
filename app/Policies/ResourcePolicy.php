<?php

namespace App\Policies;

use App\Models\Resource;
use App\Models\User;
use App\Traits\HasPermissionCheck;
use Illuminate\Auth\Access\HandlesAuthorization;

class ResourcePolicy
{
    use HandlesAuthorization, HasPermissionCheck;

    public function viewAny(User $user): bool
    {
        return $this->checkPermission($user, 'read resources');
    }

    public function view(User $user, Resource $resource): bool
    {
        return $this->checkPermission($user, 'read resources');
    }

    public function create(User $user): bool
    {
        return $this->checkPermission($user, 'create resources');
    }

    public function update(User $user, Resource $resource): bool
    {
        return $this->checkPermission($user, 'update resources');
    }

    public function destroy(User $user, Resource $resource): bool
    {
        return $this->checkPermission($user, 'destroy resources');
    }

    public function download(User $user, Resource $resource): bool
    {
        return $this->checkPermission($user, 'download resources');
    }
}
