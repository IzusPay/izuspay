<?php

namespace App\Policies;

use App\Models\Domain;
use App\Models\DomainModel;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class DomainPolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can update the domain.
     */
    public function update(User $user, DomainModel $domain)
    {
        return $user->id === $domain->user_id;
    }

    /**
     * Determine whether the user can delete the domain.
     */
    public function delete(User $user, DomainModel $domain)
    {
        return $user->id === $domain->user_id;
    }
}
