<?php

declare(strict_types=1);

namespace App\Policies;

use App\Domain\Locations\Models\Location;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class LocationPolicy
{
    use HandlesAuthorization;

    public function view(User $user, Location $location): bool
    {
        /** @var \App\Domain\Trips\Models\Trip $trip */
        $trip = $location->trip;

        return $user->id === $trip->owner_id;
    }

    public function destroy(User $user, Location $location): bool
    {
        /** @var \App\Domain\Trips\Models\Trip $trip */
        $trip = $location->trip;

        return $user->id === $trip->owner_id;
    }
}
