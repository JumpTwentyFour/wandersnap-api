<?php

declare(strict_types=1);

namespace Tests\Unit\Domain\Images;

use App\Domain\Images\Actions\ResizeImageAction;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\ImageManagerStatic as Image;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

#[Group("Images")]
class ResizeImageActionTest extends TestCase
{
    #[Test]
    public function it_creates_new_images(): void
    {
        Storage::fake('local');

        Storage::putFileAs('', UploadedFile::fake()->image('hello.jpg'), 'hello.jpg');

        $action = app()->make(ResizeImageAction::class);

        $result = $action->execute('hello.jpg', 'local');

        Storage::assertExists(['hello-150.jpg', 'hello-300.jpg', 'hello-1024.jpg']);
    }

    #[Test]
    public function it_resizes_the_images(): void
    {
        Storage::fake('local');

        Storage::putFileAs('', UploadedFile::fake()->image('hello.jpg', 500, 500), 'hello.jpg');

        $action = app()->make(ResizeImageAction::class);

        $result = $action->execute('hello.jpg', 'local');

        $this->assertEquals(500, Image::make(Storage::get('hello.jpg'))->width());

        $this->assertEquals(150, Image::make(Storage::get('hello-150.jpg'))->width());
    }

    #[Test]
    public function it_stores_image_in_the_same_file_path(): void
    {
        Storage::fake('local');

        $test = Storage::putFileAs('sub-folder', UploadedFile::fake()->image('hello.jpg'), 'hello.jpg');

        $action = app()->make(ResizeImageAction::class);

        $result = $action->execute('sub-folder/hello.jpg', 'local');

        Storage::assertExists('sub-folder/hello-150.jpg');
    }
}
