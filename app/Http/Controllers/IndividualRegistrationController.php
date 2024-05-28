<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Modules\Payment\PaymentServiceInterface;
use App\Message; // Import the Message class if not already imported

class IndividualRegistrationController extends Controller
{
    /**
     * Payment service
     *
     * @var PaymentServiceInterface $paymentService
     */
    protected PaymentServiceInterface $paymentService;

    public function __construct(PaymentServiceInterface $paymentService)
    {
        $this->paymentService = $paymentService;
    }

    public function checkout(Request $request)
    {
        $metadata = $request->input('metadata', []);
        $paymentMethod = $request->input('payment_method');
        $discountcode = $request->input('discountcode');

        return $this->paymentService->createIndividualRegistration($discountcode, $paymentMethod, $metadata);
    }

}