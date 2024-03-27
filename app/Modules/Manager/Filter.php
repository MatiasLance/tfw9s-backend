<?php

namespace App\Modules\Manager;

/**
 * Contains enums for the manager module filters
 */
class Filter
{
/**
     * Sort option that sorts by fighter creation date
     * 
     * @var string SORT_LATEST
     */
    public const SORT_LATEST = 'latest';

    /**
     * Sort option that sorts by fighter creation date
     * 
     * @var string SORT_OLDEST
     */
    public const SORT_OLDEST = 'oldest';
    
    /**
     * Sort option that sorts events alphabetically.
     * 
     * @var string SORT_A_TO_Z
     */
    public const SORT_A_TO_Z = 'a_to_z';
    
    /**
     * Sort option that sorts events alphabetically.
     * 
     * @var string SORT_Z_TO_A
     */
    public const SORT_Z_TO_A = 'z_to_a';
}
