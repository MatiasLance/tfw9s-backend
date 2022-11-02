<?php

namespace App\Modules\Upload\Tests;

use App\Modules\Upload\LocalDisk;
use App\Modules\Upload\UploadInterface;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class LocalDiskTest extends TestCase
{
    /**
     * Upload service
     * 
     * @var UploadInterface $uploadService
     */
    protected UploadInterface $uploadService;

    protected string $storagePath = 'media/items/';
    // protected string $storagePath = 'public/';

    public function setUp(): void
    {
        parent::setUp();
        $this->uploadService = $this->app->make(LocalDisk::class);
    }

    public function testStore()
    {
        $this->markTestSkipped('Feature under development');
        Storage::disk('local');

        $file = UploadedFile::fake()->image('test.jpg');
        $path = $this->uploadService->upload($file);

        $this->assertFileExists(public_path('storage/' . $path));
    }
}