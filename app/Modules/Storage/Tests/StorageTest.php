<?php

namespace App\Modules\Storage\Tests;

use App\Models\Resume;
use App\Modules\Storage\Exceptions\CannotWriteSecureFileException;
use App\Modules\Storage\Storage;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage as StorageFacade;
use Tests\TestCase;

class StorageTest extends TestCase
{
    /**
     * Path to test assets
     * 
     * @var string $testAssetsPath
     */
    protected string $testAssetsPath = './tests/Assets/';

    /**
     * Path to secure container
     * 
     * @var string $secureContainerPath
     */
    protected string $secureContainerPath = './storage/app/media/secure/';

    /**
     * Storage module
     * 
     * @var Storage $storage
     */
    private Storage $storage;

    /**
     * Set up the test
     * 
     * @return void
     */
    protected function setUp(): void
    {
        parent::setUp();
        $this->storage = $this->app->make(Storage::class);
    }

    /**
     * Test storing files in public location
     * 
     * @todo Add check if file actually exist in public container
     *
     * @return void
     */
    public function testStorePublicly()
    {
        $this->markTestIncomplete('Test to be ported for WPI scope');
        $file = new UploadedFile($this->testAssetsPath . 'pn_logo.png', 'pn_logo.png', 'image/png', null, true);
        $resume = Resume::factory()->create();
        $resumePhoto = $this->storage->store($file, $resume);

        $this->assertFileExists(public_path('storage/' . $resumePhoto->path));
    }

    /**
     * Test storing file in secure container
     * 
     * @return void
     */
    public function testStoreSecurely()
    {
        $file = UploadedFile::fake()->image('test.svg');
        $isSuccess = $this->storage->storeSecure($file, 'test.svg', '/');

        $this->assertTrue($isSuccess);
        $this->assertTrue(StorageFacade::disk('secure')->exists('test.svg'));
        $this->assertFileExists(storage_path('app/media/secure/test.svg'));
    }

    /**
     * When storing a secure file. It must not overwrite existing file
     * 
     * @return void
     */
    public function testStoreSecureMustNotOverwrite()
    {
        $file = UploadedFile::fake()->image('test.svg');
        
        $this->assertTrue(StorageFacade::disk('secure')->exists('test.svg'));
        $this->assertFileExists(storage_path('app/media/secure/test.svg'));
        $this->expectException(CannotWriteSecureFileException::class);
        $this->storage->storeSecure($file, 'test.svg', '/');
    }

    /**
     * Test deleting securefile
     * 
     * @return void
     */
    public function testDeleteSecureFile()
    {
        $isSuccess = $this->storage->deleteSecure('test.svg');

        $this->assertTrue($isSuccess);
        $this->assertFalse(file_exists(storage_path('app/media/secure/test.svg')));
    }
}
