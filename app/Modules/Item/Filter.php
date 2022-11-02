<?php

namespace App\Modules\Item;

/**
 * Contains enums for the Item module filters
 */
class Filter
{
    /**
     * Sort option that sorts by item creation date
     * 
     * @var string SORT_LATEST
     */
    public const SORT_LATEST = 'latest';
    
    /**
     * Sort option that sorts by price from low to high
     * 
     * @var string SORT_LOW_TO_HIGH
     */
    public const SORT_LOW_TO_HIGH = 'low_to_high';
    
    /**
     * Sort option that sorts by price from high to low
     * 
     * @var string SORT_HIGH_TO_LOW
     */
    public const SORT_HIGH_TO_LOW = 'high_to_low';
    
    /**
     * Sort option that sorts items alphabetically.
     * 
     * @var string SORT_A_TO_Z
     */
    public const SORT_A_TO_Z = 'a_to_z';
    
    /**
     * Sort option that sorts items alphabetically.
     * 
     * @var string SORT_Z_TO_A
     */
    public const SORT_Z_TO_A = 'z_to_a';
}