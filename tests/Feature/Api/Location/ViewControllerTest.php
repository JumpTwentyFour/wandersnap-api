<?php

declare(strict_types=1);

namespace Tests\Feature\Api\Location;

use App\Domain\Locations\Models\Location;
use App\Domain\Locations\Models\LocationImage;
use App\Domain\Trips\Models\Trip;
use App\Models\User;
use Laravel\Sanctum\Sanctum;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

#[Group('Locations')]
class ViewControllerTest extends TestCase
{
    #[Test]
    public function it_will_return_not_authenticated_response_to_not_allow_the_user_to_view_it(): void
    {
        $location = Location::factory()->create();

        $this->getJson(route('api.locations.view', $location->id))
            ->assertUnauthorized();
    }

    #[Test]
    public function not_found_returned_when_trying_to_access_location_that_doesnt_exist(): void
    {
        Sanctum::actingAs(User::factory()->create());

        $this->getJson(route('api.locations.view', 100))
            ->assertNotFound();
    }

    #[Test]
    public function it_can_not_view_a_location_from_a_trip_they_dont_own(): void
    {
        $user = User::factory()->create();

        $userTwo = User::factory()->create();

        Sanctum::actingAs($user);

        $trip = Trip::factory()->create(
            [
                'owner_id' => $userTwo->id,
            ]
        );

        $location = Location::factory()->for($trip)->create();

        $this->getJson(route('api.locations.view', $location->id))
            ->assertForbidden();
    }

    #[Test]
    public function correct_location_returned_when_one_is_found_with_no_images(): void
    {
        $user = User::factory()->create();

        Sanctum::actingAs($user);

        $trip = Trip::factory()->create(
            [
                'owner_id' => $user->id,
            ]
        );

        $location = Location::factory()->for($trip)->create();

        $this->getJson(route('api.locations.view', $location->id))
            ->assertOk()
            ->assertJsonFragment($location->toArray())
            ->assertJsonMissing(['images']);
    }

    #[Test]
    public function correct_location_returned_with_images_when_images_are_associated(): void
    {
        $user = User::factory()->create();

        Sanctum::actingAs($user);

        $trip = Trip::factory()->create(
            [
                'owner_id' => $user->id,
            ]
        );

        $location = Location::factory()->for($trip)->create();

        LocationImage::factory()->create([
            'location_id' => $location->id,
        ]);

        $this->getJson(route('api.locations.view', $location->id))
            ->assertOk()
            ->assertJsonFragment($location->toArray())
            ->assertJsonCount(1, 'data.images');
    }
}
