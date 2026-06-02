<?php

namespace App\Policies;

use App\Models\SightseeingObject;
use App\Models\User;

class SightseeingObjectPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasCmsAccess();
    }

    public function view(User $user, SightseeingObject $sightseeingObject): bool
    {
        return $user->hasCmsAccess();
    }

    public function create(User $user): bool
    {
        return $user->hasCmsAccess();
    }

    public function update(User $user, SightseeingObject $sightseeingObject): bool
    {
        return $user->hasCmsAccess();
    }

    public function delete(User $user, SightseeingObject $sightseeingObject): bool
    {
        return $user->isAdministrator();
    }

    public function restore(User $user, SightseeingObject $sightseeingObject): bool
    {
        return false;
    }

    public function forceDelete(User $user, SightseeingObject $sightseeingObject): bool
    {
        return $user->isAdministrator();
    }
}
