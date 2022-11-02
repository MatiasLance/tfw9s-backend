<?php

namespace App\Modules\Upload\Tests;

use App\Repository\TemporaryFileRepositoryInterface;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class UploadedFileTest extends TestCase
{
    public function testCancelMarkForDeletion()
    {
        $this->markTestSkipped('Feature under development');
        Storage::fake();
        $file = UploadedFile::fake()->image('test.jpg');
        
        $temporaryFileRepository = $this->app->make(TemporaryFileRepositoryInterface::class);
        $temporaryFile = $temporaryFileRepository->store($file);

        $this->assertFalse($temporaryFile->mark_for_deletion);
        $this->assertTrue(is_null($temporaryFile->last_accessed));

        $retrievedFile = $temporaryFileRepository->retrieve($temporaryFile->token);

        $temporaryFile->refresh();
        $this->assertTrue($temporaryFile->mark_for_deletion);
        $this->assertFalse(is_null($temporaryFile->last_accessed));

        $retrievedFile->cancelMarkForDeletion();

        $temporaryFile->refresh();
        $this->assertFalse($temporaryFile->mark_for_deletion);
        $this->assertFalse(is_null($temporaryFile->last_accessed));
    }
}