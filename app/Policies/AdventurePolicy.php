<?php

namespace App\Policies;

use App\Models\Adventure;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class AdventurePolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function create(User $user): bool
    {
        return true;
    }

    public function view(User $user, Adventure $adventure): Response
    {
        return $this->ownerResponse($user, $adventure);
    }

    public function update(User $user, Adventure $adventure): Response
    {
        return $this->ownerResponse($user, $adventure);
    }

    public function delete(User $user, Adventure $adventure): Response
    {
        return $this->ownerResponse($user, $adventure);
    }

    private function ownerResponse(User $user, Adventure $adventure): Response
    {
        return $adventure->getAttribute('user_id') === $user->id && ! $adventure->trashed()
            ? Response::allow()
            : Response::denyAsNotFound();
    }
}
