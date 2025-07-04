<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Series;
use App\Models\Tax;
use App\Models\DiscountCode;
use App\Models\ToggleTaxControl;
use App\Modules\Payment\PaymentServiceInterface;
use App\Modules\Http\Message;

class TeamRegistrationController extends Controller
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
        $item = $request->input('item');
        $metadata = $request->input('metadata', []);
        $paymentMethod = $request->input('payment_method');
        $discountcode = $request->input('discountcode');

        return $this->paymentService->createTeamRegistration($discountcode, $paymentMethod, $item, $metadata);
    }

    public function verify(Request $request, Message $message)
    {
        $paymentIntentId = $request->input('transaction_id');
        $paymentMethod = $request->input('payment_method');

        $status = $this->paymentService->verifyTeamRegistration($paymentMethod, $paymentIntentId);

        $message->setContent(200, 'Payment Intent status found', '', [
            'status' => $status
        ]);

        return $message->render();
    }

    public function initialAfterPayCalculation(Request $request)
    {
        $amount = $request->input('amount');
    
        $tax = Tax::find(1);
        $master = ToggleTaxControl::find(1);

        $addTax = $tax->addTaxValue;
        $includeTax = $tax->includeTaxValue;
        $isInclusive = $master->toggleControl2;

        $taxAmount = 0;

        if (!$isInclusive) {
            $taxRate = $addTax / 100;
            $taxAmount = $amount * $taxRate;
            $totalPrice = intval($amount + $taxAmount);
            $isInclusive = false;
        } elseif ($isInclusive) {
            $totalPrice = intval($amount);
            $isInclusive = true;
        } elseif (!$isInclusive) {
            $taxRate = $addTax / 100;
            $taxAmount = $amount * $taxRate;
            $totalPrice = intval($amount + $taxAmount);
            $isInclusive = false;
        } else {
            $totalPrice = intval($amount);
            $isInclusive = true;
        }

        return [
            'totalPrice' => $totalPrice
        ];
    }

    public function initialStripeCalculation(Request $request)
    {
        $item = $request->input('item');
        $amount = $request->input('amount');

        
        $tax = Tax::find(1);
        $master = ToggleTaxControl::find(1);
        

        $addTax = $tax->addTaxValue;
        $includeTax = $tax->includeTaxValue;
        $isInclusive = $master->toggleControl2;

        $currentItem = Series::find($item);
        $regularPrice = $currentItem->centPrice();

        $taxAmount = 0;

        if (!$isInclusive) {
            $taxRate = $addTax / 100;
            $taxAmount = $amount * $taxRate;
            $totalPrice = intval($amount + $taxAmount);
            $subTotal = intval($totalPrice / 1.1);
            $isInclusive = false;
        } elseif ($isInclusive) {
            $totalPrice = intval($amount);
            $subTotal = intval($totalPrice);
            $isInclusive = true;
        } elseif (!$isInclusive) {
            $taxRate = $addTax / 100;
            $taxAmount = $amount * $taxRate;
            $totalPrice = intval($amount + $taxAmount);
            $subTotal = intval($totalPrice / 1.1);
            $isInclusive = false;
        } else {
            $totalPrice = intval($amount);
            $subTotal = intval($totalPrice);
            $isInclusive = true;
        }

        $taxAmount = intval(round($taxAmount));

        return response()->json([
            'taxAmount' => $taxAmount,
            'totalPrice' => $totalPrice * 100,
            'subTotal' => $subTotal * 100
        ]);
    }

}
