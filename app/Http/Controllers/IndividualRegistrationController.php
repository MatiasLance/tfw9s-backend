<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Series;
use App\Models\Tax;
use App\Models\DiscountCode;
use App\Models\ToggleTaxControl;
use App\Modules\Payment\PaymentServiceInterface;
use App\Modules\Http\Message;

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
        $item = $request->input('item');
        $metadata = $request->input('metadata', []);
        $paymentMethod = $request->input('payment_method');
        $discountcode = $request->input('discountcode');

        $photo = null;
        if ($request->hasFile('photo')) {
            $photo = [
                'file' => $request->file('photo'),
                'type' => 'photo'
            ];
        }

        return $this->paymentService->createIndividualRegistration($discountcode, $paymentMethod, $item, $metadata);
    }

    public function verify(Request $request, Message $message)
    {
        $paymentIntentId = $request->input('transaction_id');
        $paymentMethod = $request->input('payment_method');

        $status = $this->paymentService->verifyIndividualRegistration($paymentMethod, $paymentIntentId);

        $message->setContent(200, 'Payment Intent status found', '', [
            'status' => $status
        ]);

        return $message->render();
    }

    public function initialAfterPayCalculation(Request $request)
    {
        $amount = $request->input('amount');
        $discountCodeID = $request->input('discountID');
    
        $tax = Tax::find(1);
        $master = ToggleTaxControl::find(1);
        $discountCode = DiscountCode::where('id', $discountCodeID)->first();
        $discountRate = floatval($discountCode->rate);

        $addTax = $tax->addTaxValue;
        $includeTax = $tax->includeTaxValue;
        $isInclusive = $master->toggleControl2;

        $taxAmount = 0;

        if (!$isInclusive && $discountRate != 0.0) {
            $taxRate = $addTax / 100;
            $price = $amount * (1 - $discountRate);
            $taxAmount = $amount * $taxRate;
            $totalPrice = intval($price + $taxAmount);
            $isInclusive = false;
        } elseif ($isInclusive && $discountRate != 0.0) {
            $price = $amount * (1 - $discountRate);
            $totalPrice = intval($price);
            $isInclusive = true;
        } elseif (!$isInclusive && $discountRate === 0.0) {
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
        $discountCodeID = $request->input('discountID');

        
        $tax = Tax::find(1);
        $master = ToggleTaxControl::find(1);
        $discountCode = DiscountCode::where('id', $discountCodeID)->first();
        $discountRate = floatval($discountCode->rate);
        

        $addTax = $tax->addTaxValue;
        $includeTax = $tax->includeTaxValue;
        $isInclusive = $master->toggleControl2;

        $currentItem = Series::find($item);
        $regularPrice = $currentItem->centPrice();

        $taxAmount = 0;

        if (!$isInclusive && $discountRate != 0.0) {
            $taxRate = $addTax / 100;
            $price = $amount * (1 - $discountRate);
            $taxAmount = $amount * $taxRate;
            $totalPrice = intval($price + $taxAmount);
            $subTotal = intval($totalPrice / 1.1);
            $isInclusive = false;
        } elseif ($isInclusive && $discountRate != 0.0) {
            $price = $amount * (1 - $discountRate);
            $totalPrice = intval($price);
            $subTotal = intval($totalPrice);
            $isInclusive = true;
        } elseif (!$isInclusive && $discountRate === 0.0) {
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