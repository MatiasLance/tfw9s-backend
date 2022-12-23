<?php

namespace App\Modules\Payment;

enum PaymentStatus: String
{
    /** 
     * Pending status
     * 
     * A payment is in pending status when it is requiring additional input, like waiting for the
     * user to authorize the payment
     */
    case PENDING = 'pending';

    /**
     * Processing status
     * 
     * A payment in is processing when the user has already authorized the payment but the payment gateway
     * is still processing the transaction
     */
    case PROCESSING = 'processing';

    /**
     * Complete status
     * 
     * A payment is complete when the transaction has completed and the amount has been transferred.
     */
    case COMPLETE = 'complete';

    /**
     * Failed status
     * 
     * A payment is failed when there is an error in the transaction process.
     */
    case FAILED = 'failed';

    /**
     * Cancelled status
     * 
     * A payment is cancelled when the user or the merchant has cancelled the transaction.
     */
    case CANCELLED = 'cancelled';
}