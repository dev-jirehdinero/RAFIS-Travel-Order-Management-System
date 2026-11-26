<?php

namespace App\Policies;

use App\Enums\Role;
use App\Models\User;

/** Auto-discovered (App\Models\User -> App\Policies\UserPolicy). */
class UserPolicy
{
    /** Admin can do everything. */
    public function before(User $user, string $ability): ?bool
    {
        return $user->isAdmin() ? true : null;
    }

    /** See the list of accounts. */
    public function viewAny(User $user): bool
    {
        return $user->isOic();
    }

    /** Create accounts. Which roles an OIC may hand out is limited in User::assignableRoles(). */
    public function create(User $user): bool
    {
        return $user->isOic();
    }

    /**
     * Edit another account's name / role and reset its password.
     * An OIC can only manage Staff accounts (so never OIC or Admin accounts, including their own).
     */
    public function update(User $user, User $target): bool
    {
        return $user->isOic() && $target->role === Role::Staff;
    }
}
