<?php

namespace App\Modules\Discount;

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
