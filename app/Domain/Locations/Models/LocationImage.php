<?php

declare(strict_types=1);

namespace App\Domain\Locations\Models;

use App\Domain\Images\Actions\ResizeImageAction;
use Database\Factories\LocationImageFactory;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

use function Illuminate\Events\queueable;

class LocationImage extends Model
{
    use SoftDeletes;
    use HasFactory;
    use HasUlids;

    protected $fillable = [
        'id',
        'location_id',
        'name',
        'path',
    ];

    protected static function booted(): void
    {
        static::created(queueable(function (self $image) {
            if (!$image->path) {
                return;
            }

            app(ResizeImageAction::class)->execute(
                $image->path,
                config('filament.location_images_filesystem')
            );
        }));
    
        static::updated(queueable(function (self $image) {
            if (!array_key_exists('path', $image->getChanges())) {
                return;
            }

            app(ResizeImageAction::class)->execute(
                $image->path,
                config('filament.location_images_filesystem')
            );
        }));
    }

    protected static function newFactory(): LocationImageFactory
    {
        return LocationImageFactory::new();
    }
}
