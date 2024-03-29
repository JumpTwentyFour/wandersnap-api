<?php

declare(strict_types=1);

namespace Tests\Feature\Api\Trips;

use App\Domain\Trips\Models\Trip;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Laravel\Sanctum\Sanctum;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

#[Group('Trips')]
class StoreControllerTest extends TestCase
{
    use RefreshDatabase;
    use WithFaker;

    #[Test]
    public function it_cannot_create_a_trip_when_not_authorised(): void
    {
        $response = $this->postJson(route('api.trips.store'));

        $response->assertUnauthorized();
    }

    #[Test]
    public function it_can_post_when_authorised_but_not_store_as_validation_is_failed(): void
    {
        $user = User::factory()->create();

        Sanctum::actingAs($user);

        $response = $this->postJson(route('api.trips.store'));

        $response->assertInvalid(['name', 'start_date', 'end_date']);
    }

    #[Test]
    public function it_fails_validation_when_end_date_is_before_start_date(): void
    {
        $user = User::factory()->create();

        Sanctum::actingAs($user);

        $postData = [
            'name' => $this->faker->name(),
            'start_date' => now()->addWeek()->format('Y-m-d'),
            'end_date' => now()->format('Y-m-d'),
        ];

        $response = $this->postJson(route('api.trips.store'), $postData);

        $response->assertInvalid(['end_date']);
    }

    #[Test]
    public function it_creates_a_trip_without_an_image(): void
    {
        $user = User::factory()->create();

        Sanctum::actingAs($user);

        $postData = [
            'name' => $this->faker->name(),
            'start_date' => now()->addWeek()->format('Y-m-d'),
            'end_date' => now()->addWeeks(2)->format('Y-m-d'),
        ];

        $response = $this->postJson(route('api.trips.store'), $postData);

        $response->assertCreated();

        $this->assertDatabaseCount(Trip::class, 1);

        $this->assertDatabaseHas(
            Trip::class,
            [
                'name' => $postData['name'],
                'start_date' => $postData['start_date'],
                'end_date' => $postData['end_date'],
            ]
        );
    }

    #[Test]
    public function it_creates_a_report_with_an_image(): void
    {
        $user = User::factory()->create();

        Sanctum::actingAs($user);

        $file = UploadedFile::fake()->image('avatar.jpg');

        $postData = [
            'name' => $this->faker->name(),
            'start_date' => now()->addWeek()->format('Y-m-d'),
            'end_date' => now()->addWeeks(2)->format('Y-m-d'),
            'cover_photo' => $file,
        ];

        $response = $this->postJson(route('api.trips.store'), $postData);

        $trip = $response->json('data');

        $newFileName = "{$trip['id']}/cover_image/";

        $coverPhotoFileName = $newFileName."{$trip['id']}_cover_photo.{$file->getClientOriginalExtension()}";

        $this->assertDatabaseHas(
            Trip::class,
            [
                'name' => $postData['name'],
                'start_date' => $postData['start_date'],
                'end_date' => $postData['end_date'],
                'cover_photo' => $coverPhotoFileName,
            ]
        );

        Storage::disk(config('filesystems.default'))->assertExists($coverPhotoFileName);
    }
}
