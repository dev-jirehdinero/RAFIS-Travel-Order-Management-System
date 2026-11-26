<?php

namespace App\Policies;

use App\Models\TravelOrder;
use App\Models\User;

/**
 * Auto-discovered by Laravel (App\Models\TravelOrder -> App\Policies\TravelOrderPolicy),
 * so nothing needs registering in a service provider.
 */
class TravelOrderPolicy
{
    /** Admin can do everything. */
    public function before(User $user, string $ability): ?bool
    {
        return $user->isAdmin() ? true : null;
    }

    /** See the full registry + Accomplishment Summary grid. */
    public function viewAny(User $user): bool
    {
        return $user->isOic();
    }

    public function view(User $user, TravelOrder $order): bool
    {
        return $user->isOic() || $this->owns($user, $order);
    }

    /** "+ New Registry". */
    public function create(User $user): bool
    {
        return $user->isOic();
    }

    /** Change the status marker (Not started -> SUBMITTED -> APPROVED). */
    public function update(User $user, TravelOrder $order): bool
    {
        return $user->isOic();
    }

    /** Upload a completion file: the owner, or an OIC. */
    public function upload(User $user, TravelOrder $order): bool
    {
        return $user->isOic() || $this->owns($user, $order);
    }

    private function owns(User $user, TravelOrder $order): bool
    {
        return $user->personnel_id !== null && $user->personnel_id === $order->personnel_id;
    }
}
