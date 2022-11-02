<?php

namespace App\Modules\Upload\Tests;

use App\Modules\Upload\Classes\UploadedFile as TemporaryUploadedFile;
use App\Repository\TemporaryFileRepositoryInterface;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class TemporaryFileRepositoryTest extends TestCase
{
    use RefreshDatabase;

    protected TemporaryFileRepositoryInterface $temporaryFileRepository;

    // public function setUp(): void
    // {
    //     parent::setUp();
    //     $this->temporaryFileRepository = $this->app->make(TemporaryFileRepositoryInterface::class);
    // }

    public function testRetrieve()
    {
        $this->markTestSkipped('Feature not in project scope');
        Storage::fake();
        $file = UploadedFile::fake()->image('test.jpg');
        $fileHash = hash_file('sha256', $file);
        $temporaryFile = $this->temporaryFileRepository->store($file);
        
        $retrievedFile = $this->temporaryFileRepository->retrieve($temporaryFile->token);

        $this->assertTrue($retrievedFile instanceof UploadedFile);
        $this->assertTrue($retrievedFile instanceof TemporaryUploadedFile);
        // $this->assertEquals($fileHash, hash_file('sha256', $retrievedFile));
    }
}