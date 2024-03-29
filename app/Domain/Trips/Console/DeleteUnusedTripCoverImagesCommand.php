<?php

declare(strict_types=1);

namespace App\Domain\Trips\Console;

use App\Domain\Trips\Models\Trip;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

class DeleteUnusedTripCoverImagesCommand extends Command
{
    protected $signature = 'trips:unused-trip-cover-images';

    protected $description = 'Deletes all unused trip cover images from S3';

    public function handle(): void
    {
        $currentTripCoverImages = Trip::query()->pluck('cover_photo')->toArray();

        $storage = Storage::disk(config()->get('filament.trip_cover_images_filesystem'));
        collect($storage->allFiles())
            ->reject(fn (string $file) => in_array($file, $currentTripCoverImages, true))
            ->each(fn ($file) => $storage->delete($file));
    }
}
