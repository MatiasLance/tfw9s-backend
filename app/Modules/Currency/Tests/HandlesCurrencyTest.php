<?php

namespace App\Modules\Currency\Tests;

use App\Modules\Currency\Traits\HandlesCurrency;
use PHPUnit\Framework\TestCase;

class HandlesCurrencyTest extends TestCase
{
    /**
     * Anonymous Class that uses HandlesCurrency trait
     * 
     * @var $handlesCurrency
     */
    protected $handlesCurrency;

    protected function setUp(): void
    {
        parent::setUp();

        $this->handlesCurrency = new class {
            use HandlesCurrency;
        };
    }

    /**
     * Test converting price to its cent value
     * 
     * @dataProvider convertToCentProvider
     * 
     * @param float|int $value The price to convert to cent
     * @param int $expected The expected cent value of the price
     * 
     * @return void
     */
    public function testPriceToCentValue($value, $expected): void
    {
        $centValue = $this->handlesCurrency->toCent($value);

        $this->assertEquals($expected, $centValue);
    }

    /**
     * Test converting centValue back to its price
     * 
     * @dataProvider convertToPriceProvider
     * 
     * @param int $value
     * @param float $expected
     * 
     * @return void
     */
    public function testCentValueToPrice($value, $expected): void
    {
        $price = $this->handlesCurrency->toPrice($value);

        $this->assertEquals($expected, $price);
    }

    /**
     * Test converting prices to cent and then back to prices
     * 
     * @dataProvider convertFromAndToCentvalueProvider
     * 
     * @param float $value
     * @param float $expected
     * 
     * @return void
     */
    public function testConvertingFromAndToCentValue($value, $expected): void
    {
        $centValue = $this->handlesCurrency->toCent($value);
        $priceValue = $this->handlesCurrency->toPrice($centValue);

        $this->assertEquals($expected, $priceValue);
    }

    /**
     * Provider to the PriceToCentValue test
     * 
     * @return array
     */
    public function convertToCentProvider(): array
    {
        return [
            "Whole number" => [120.00, 12000],
            "Decimal number in hundredths" => [0.75, 75],
            "Decimal number in tenths" => [0.05, 5],
            "Negative number" => [-12.50, -1250],
            "Whole number with decimal number" => [512.35, 51235],
            "Precise float" => [44.215, 4421], // Drops decimal places greater than hundreths
            "Big number" => [8962366.357, 896236635],
            "Very low number" => [0.00999, 0],
            "Zero" => [0, 0],
        ];
    }

    /**
     * Provider to the CentValueToPrice test
     * 
     * @return array
     */
    public function convertToPriceProvider(): array
    {
        return [
            "Whole number" => [1000, 10],
            "Decimal number" => [35, 0.35],
            "Number with decimal in hundredths" => [82398, 823.98],
            "Number with decimal in tenths" => [12560, 125.6],
            "Negative number" => [-32344, -323.44],
            "Big number" => [896236635, 8962366.35],
            "Zero" => [0, 0],
        ];
    }

    /**
     * Provider to the ConvertingFromAndToCentValue test
     * 
     * @see https://www.php.net/manual/en/function.intval.php For Edge Cases regarding floating point
     * 
     * @return array
     */
    public function convertFromAndToCentvalueProvider(): array
    {
        return [
            "Integers" => [26, 26],
            "Floats in tenths" => [22.4, 22.4],
            "Floats in hundredths" => [14.75, 14.75],
            "Floats with zero padding to the right" => [33.20, 33.2],
            "Negative Numbers" => [-66.23, -66.23],
            "Zeroes" => [0, 0],
            "Precise float" => [35.995, 35.99],
            "Edge Case: Floating Point issue #1" => [10.2, 10.2], // 10.2 returns as 10.19
            "Edge Case: Floating Point issue #2" => [19.99, 19.99], // 10.2 returns as 10.19
        ];
    }
}