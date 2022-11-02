<?php

namespace App\Modules\Media\Tests;

use App\Modules\Media\Classes\MimeType;
use App\Modules\Media\Exceptions\InvalidValidateOptionException;
use App\Modules\Media\Traits\ValidatesMedia;
use Tests\TestCase;

/**
 * Test Media Validation
 * 
 * PHPUnit TestCase class is used to skip initialization of Laravel
 * related functions and therefore decrease the time spent when
 * running the tests.
 */
class ValidateTest extends TestCase
{
    /**
     * Anonymous Class that uses the ValidatesMedia trait
     * 
     * Anonymous class is used to avoid confusion when calling out the trait functions. By using
     * anonumous class, we call the trait as if it is a class and not using $this.
     * 
     * @see https://www.php.net/manual/en/language.oop5.anonymous.php For php anonumous classes
     * 
     * @var class $validatesMedia
     */
    protected $validatesMedia;

    protected function setUp(): void
    {
        parent::setUp();

        $this->validatesMedia = new class {
            use ValidatesMedia;
        };
    }

    /**
     * Test validating media
     * 
     * @return void
     */
    public function testValidation()
    {
        $filename = app_path('Modules/Media/Tests/Assets/pn_logo.png');
        $isValid = $this->validatesMedia->validateMedia($filename);

        $this->assertTrue($isValid);

        $filename = app_path('Modules/Media/Tests/Assets/svg_pn_logo.svg');
        $isValid = $this->validatesMedia->validateMedia($filename);

        $this->assertFalse($isValid);
    }

    /**
     * Test validating media with images only option
     */
    public function testValidateImagesOnly()
    {
        $filename = app_path('Modules/Media/Tests/Assets/pn_logo.png');
        $this->validatesMedia->setAllowedImages([MimeType::PNG]);
        $isValid = $this->validatesMedia->validateMedia($filename, MimeType::VALIDATE_IMAGE);

        $this->assertTrue($isValid);

        $filename = app_path('Modules/Media/Tests/Assets/pexels-cottonbro-6865077.mp4');
        $isValid = $this->validatesMedia->validateMedia($filename, MimeType::VALIDATE_IMAGE);

        $this->assertFalse($isValid);
    }

    /**
     * Test validating media with videos only option
     */
    public function testValidateVideoOnly()
    {
        $filename = app_path('Modules/Media/Tests/Assets/pexels-cottonbro-6865077.mp4');
        $this->validatesMedia->setAllowedVideos([MimeType::MP4]);
        $isValid = $this->validatesMedia->validateMedia($filename, MimeType::VALIDATE_VIDEO);

        $this->assertTrue($isValid);

        $filename = app_path('Modules/Media/Tests/Assets/pn_logo.png');
        $isValid = $this->validatesMedia->validateMedia($filename, MimeType::VALIDATE_VIDEO);

        $this->assertFalse($isValid);
    }

    /**
     * Test validating media with invalid option given
     */
    public function testInvalidValidateOption()
    {
        $filename = app_path('Modules/Media/Tests/Assets/pn_logo.png');

        $this->expectException(InvalidValidateOptionException::class);
        $this->validatesMedia->validateMedia($filename, -1);
    }

    public function testSetAllowedMedia()
    {
        $allowedMedia = [
            MimeType::JPG,
            MimeType::SVG,
            MimeType::MP4,
        ];

        $this->validatesMedia->setAllowedMedia($allowedMedia);

        $this->assertEquals($allowedMedia, $this->validatesMedia->getAllowedMedia());

        $filename = app_path('Modules/Media/Tests/Assets/svg_pn_logo.svg');
        $isValid = $this->validatesMedia->validateMedia($filename);
        
        $this->assertTrue($isValid);

        $filename = app_path('Modules/Media/Tests/Assets/pn_logo.png');
        $isValid = $this->validatesMedia->validateMedia($filename);
        
        $this->assertFalse($isValid);
    }

    public function testAllowDisallowMedia()
    {
        $filename = app_path('Modules/Media/Tests/Assets/svg_pn_logo.svg');

        $isValid = $this->validatesMedia->validateMedia($filename);
        $this->assertFalse($isValid);
        
        $this->validatesMedia->allowMedia(MimeType::SVG);

        $isValid = $this->validatesMedia->validateMedia($filename);
        $this->assertTrue($isValid);
        
        $this->validatesMedia->disallowMedia(MimeType::SVG);

        $isValid = $this->validatesMedia->validateMedia($filename);
        $this->assertFalse($isValid);
    }

    public function testSetAllowedImages()
    {
        $allowedImages = [
            MimeType::JPG,
            MimeType::SVG,
        ];

        $this->validatesMedia->setAllowedImages($allowedImages);

        $this->assertEquals($allowedImages, $this->validatesMedia->getAllowedImages());

        $filename = app_path('Modules/Media/Tests/Assets/svg_pn_logo.svg');
        $isValid = $this->validatesMedia->validateMedia($filename, MimeType::VALIDATE_IMAGE);
        
        $this->assertTrue($isValid);

        $filename = app_path('Modules/Media/Tests/Assets/pn_logo.png');
        $isValid = $this->validatesMedia->validateMedia($filename, MimeType::VALIDATE_IMAGE);
        
        $this->assertFalse($isValid);
    }

    public function testAllowDisallowImages()
    {
        $filename = app_path('Modules/Media/Tests/Assets/svg_pn_logo.svg');

        $isValid = $this->validatesMedia->validateMedia($filename, MimeType::VALIDATE_IMAGE);
        $this->assertFalse($isValid);
        
        $this->validatesMedia->allowImages(MimeType::SVG);

        $isValid = $this->validatesMedia->validateMedia($filename, MimeType::VALIDATE_IMAGE);
        $this->assertTrue($isValid);
        
        $this->validatesMedia->disallowImages(MimeType::SVG);

        $isValid = $this->validatesMedia->validateMedia($filename, MimeType::VALIDATE_IMAGE);
        $this->assertFalse($isValid);
    }

    public function testSetAllowedVideos()
    {
        $allowedVideos = [
            MimeType::MP4,
            MimeType::MPEG,
        ];

        $this->validatesMedia->setAllowedVideos($allowedVideos);

        $this->assertEquals($allowedVideos, $this->validatesMedia->getAllowedVideos());

        $filename = app_path('Modules/Media/Tests/Assets/pexels-cottonbro-6865077.mp4');
        $isValid = $this->validatesMedia->validateMedia($filename, MimeType::VALIDATE_VIDEO);
        
        $this->assertTrue($isValid);
    }

    public function testAllowDisallowVideos()
    {
        $this->validatesMedia->setAllowedVideos([]);

        $filename = app_path('Modules/Media/Tests/Assets/pexels-cottonbro-6865077.mp4');

        $isValid = $this->validatesMedia->validateMedia($filename, MimeType::VALIDATE_VIDEO);
        $this->assertFalse($isValid);
        
        $this->validatesMedia->allowVideos(MimeType::MP4);

        $isValid = $this->validatesMedia->validateMedia($filename, MimeType::VALIDATE_VIDEO);
        $this->assertTrue($isValid);
        
        $this->validatesMedia->disallowVideos(MimeType::MP4);

        $isValid = $this->validatesMedia->validateMedia($filename, MimeType::VALIDATE_VIDEO);
        $this->assertFalse($isValid);

    }
}