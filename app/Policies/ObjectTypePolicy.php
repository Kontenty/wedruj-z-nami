<?php

namespace App\Policies;

use App\Models\ObjectType;
use App\Models\User;

class ObjectTypePolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasCmsAccess();
    }

    public function view(User $user, ObjectType $objectType): bool
    {
        return $user->hasCmsAccess();
    }

    public function create(User $user): bool
    {
        return $user->hasCmsAccess();
    }

    public function update(User $user, ObjectType $objectType): bool
    {
        return $user->hasCmsAccess();
    }

    public function delete(User $user, ObjectType $objectType): bool
    {
        return $user->isAdministrator();
    }

    public function restore(User $user, ObjectType $objectType): bool
    {
        return false;
    }

    public function forceDelete(User $user, ObjectType $objectType): bool
    {
        return $user->isAdministrator();
    }
}
