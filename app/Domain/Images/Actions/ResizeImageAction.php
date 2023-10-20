<?php

declare(strict_types=1);

namespace App\Domain\Images\Actions;

use Illuminate\Support\Facades\Storage;
use Intervention\Image\ImageManagerStatic as Image;

class ResizeImageAction
{
    private const SIZES = [
        150,
        300,
        1024,
    ];

    public function execute(string $fileName, ?string $disk): void
    {
        if (Storage::disk($disk)->missing($fileName)) {
            return;
        }

        $imageContents = Storage::disk($disk)->get($fileName);

        foreach (self::SIZES as $size) {
            $image = Image::make($imageContents);

            $image->resize($size, $size, function ($constraint) {
                $constraint->aspectRatio();
                $constraint->upsize();
            });

            $test = Storage::disk($disk)->put($this->resizedFileName($fileName, $size), $image->stream());

            $image->destroy();
        }
    }

    private function resizedFileName(string $fileName, int $size): string
    {
        $pathParts = pathinfo($fileName);

        $pathParts['dirname'] = $pathParts['dirname'] ?? '';

        if ($pathParts['dirname'] === '.' || $pathParts['dirname'] === '/') {
            $pathParts['dirname'] = '';
        }

        if ($pathParts['dirname']) {
            $pathParts['dirname'] .= '/';
        }

        return $pathParts['dirname'] . $pathParts['filename'] . '-' . $size . '.' . $pathParts['extension'];
    }
}
