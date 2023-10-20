<?php

declare(strict_types=1);

namespace App\Domain\Trips\Models;

use App\Domain\Images\Actions\ResizeImageAction;
use App\Domain\Locations\Models\Location;
use App\Domain\Trips\Collections\TripCollection;
use App\Models\User;
use Database\Factories\TripFactory;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

use function Illuminate\Events\queueable;

class Trip extends Model
{
    use SoftDeletes;
    use HasFactory;
    use HasUlids;

    protected $fillable = [
        'id',
        'owner_id',
        'name',
        'start_date',
        'end_date',
        'cover_photo',
    ];

    protected $casts = [
        'start_date' => 'datetime',
        'end_date' => 'datetime',
    ];

    protected static function booted(): void
    {
        static::created(queueable(function (self $trip) {
            if (!$trip->cover_photo) {
                return;
            }

            app(ResizeImageAction::class)->execute(
                $trip->cover_photo,
                config('filament.trip_cover_images_filesystem')
            );
        }));

        static::updated(queueable(function (self $trip) {
            if (!array_key_exists('cover_photo', $trip->getChanges())) {
                return;
            }

            app(ResizeImageAction::class)->execute(
                $trip->cover_photo,
                config('filament.trip_cover_images_filesystem')
            );
        }));
    }

    /**
     * @phpstan-return BelongsTo<\App\Models\User, \App\Domain\Trips\Models\Trip>
     */
    public function owner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'owner_id', 'id');
    }

    /**
     * @phpstan-return HasMany<\App\Domain\Locations\Models\Location>
     */
    public function locations(): HasMany
    {
        return $this->hasMany(Location::class, 'trip_id');
    }

    protected static function newFactory(): TripFactory
    {
        return TripFactory::new();
    }

    /**
     * @param  array<int, \App\Domain\Trips\Models\Trip> $models
     * @return \App\Domain\Trips\Collections\TripCollection<int, \App\Domain\Trips\Models\Trip>
     */
    public function newCollection(array $models = []): TripCollection
    {
        return new TripCollection($models);
    }
}
