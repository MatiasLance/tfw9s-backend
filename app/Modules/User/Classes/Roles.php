<?php

namespace App\Modules\User\Classes;

class Roles
{
    
    /**
     * Superadmin User role
     * 
     * @var string SUPERADMIN
     */
    public const SUPERADMIN = 'superadmin';
    
    /**
     * Admin User role
     * 
     * @var string ADMIN
     */
    public const ADMIN = 'admin';
    
    /**
     * Customer User role
     * 
     * @var string CUSTOMER
     */
    public const CUSTOMER = 'customer';

    /**
     * Array of available roles. The keys are the role values and the array value are the human readble format of the roles.
     * 
     * @var array ROLE_TABLE
     */
    public const ROLE_TABLE = [
        'superadmin' => 'Super Admin',
        'admin' => 'Admin',
        'customer' => 'Customer',
    ];
}