<?php

namespace App\Policies;

use App\Models\User;

class UserPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->isAdministrator();
    }

    public function view(User $user, User $record): bool
    {
        return $user->isAdministrator();
    }

    public function create(User $user): bool
    {
        return $user->isAdministrator();
    }

    public function update(User $user, User $record): bool
    {
        return $user->isAdministrator();
    }

    public function delete(User $user, User $record): bool
    {
        if (! $user->isAdministrator() || $user->is($record)) {
            return false;
        }

        if ($record->isAdministrator()) {
            return User::query()
                ->where('role', User::ROLE_ADMINISTRATOR)
                ->count() > 1;
        }

        return true;
    }
}
