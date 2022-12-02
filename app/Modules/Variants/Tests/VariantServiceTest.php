<?php

namespace App\Modules\Variants\Tests;

use App\Modules\Variants\VariantService;
use App\Repository\VariantRepositoryInterface;
use Tests\TestCase;

class VariantServiceTests extends TestCase
{
    /**
     * Test the validation for color hex code 
     * 
     * @dataProvider hexCodeTestDataProvider
     */
    public function test_hex_code_validator(string $hex, bool $expectedValidity)
    {
        $variantRepository = $this->app->make(VariantRepositoryInterface::class);
        $exposedVariantServiceClass = new class($variantRepository) extends VariantService {
            public function __construct(VariantRepositoryInterface $variantRepository)
            {
                parent::__construct($variantRepository);
            }
            public function exposeColorHexValidator(string $hex)
            {
                return $this->isValidColorHex($hex);
            }
        };
        $isHexValid = $exposedVariantServiceClass->exposeColorHexValidator($hex);
        
        $this->assertEquals($isHexValid, $expectedValidity);
    }

    protected function hexCodeTestDataProvider()
    {
        return [
            '3 Digits' => [
                'hex' => '#12a',
                'isValid' => true,
            ],
            '3 Letters' => [
                'hex' => '#fff',
                'isValid' => true,
            ],
            '6 Digits' => [
                'hex' => '#123456',
                'isValid' => true,
            ],
            '6 Letters' => [
                'hex' => '#ffffff',
                'isValid' => true,
            ],
            '3 Alphanumeric' => [
                'hex' => '#0fa',
                'isValid' => true,
            ],
            '6 Alphanumeric' => [
                'hex' => '#0faabc',
                'isValid' => true,
            ],
            'Lowercase' => [
                'hex' => '#0fa45e',
                'isValid' => true,
            ],
            'Uppercase' => [
                'hex' => '#0FA45E',
                'isValid' => true,
            ],
            'Mixed case' => [
                'hex' => '#0FA45e',
                'isValid' => true,
            ],
            '4 digits' => [
                'hex' => '#0024',
                'isValid' => false,
            ],
            '4 alphanumeric' => [
                'hex' => '#008d',
                'isValid' => false,
            ],
            '8 alphanumeric' => [
                'hex' => '#008d125a',
                'isValid' => false,
            ],
            'Missing #' => [
                'hex' => '15c28b',
                'isValid' => false,
            ],
            'Invalid hex value lowercase' => [
                'hex' => '#a2564z',
                'isValid' => false,
            ],
            'Invalid hex value uppercase' => [
                'hex' => '#A2564Z',
                'isValid' => false,
            ],
            'Invalid character' => [
                'hex' => '#712-241',
                'isValid' => false,
            ],
            'Whitespace in between' => [
                'hex' => '#712 241',
                'isValid' => false,
            ],
            'Whitespace suffixed' => [
                'hex' => '#712241 ',
                'isValid' => false,
            ],
        ];
    }
}