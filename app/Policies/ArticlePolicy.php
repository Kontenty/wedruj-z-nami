<?php

namespace App\Policies;

use App\Models\Article;
use App\Models\User;

class ArticlePolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasCmsAccess();
    }

    public function view(User $user, Article $article): bool
    {
        return $user->hasCmsAccess();
    }

    public function create(User $user): bool
    {
        return $user->hasCmsAccess();
    }

    public function update(User $user, Article $article): bool
    {
        return $user->hasCmsAccess();
    }

    public function delete(User $user, Article $article): bool
    {
        return $user->isAdministrator();
    }

    public function restore(User $user, Article $article): bool
    {
        return false;
    }

    public function forceDelete(User $user, Article $article): bool
    {
        return $user->isAdministrator();
    }
}
