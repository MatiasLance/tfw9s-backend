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

    public function calculation(Request $request)
    {
        $paymentIntent = $request->input('paymentIntent');
        $paymentMethod = $request->input('paymentMethod');

        $item = $request->input('item');
        $tax = Tax::find(1);
        $discountcode = $request->input('discountcode');
        $res = DiscountCode::where('code', $discountcode)->first();
        $master = ToggleTaxControl::find(1);

        $addTax = $tax->addTaxValue;
        $includeTax = $tax->includeTaxValue;
        $isInclusive = $master->toggleControl2;

        $currentItem = Series::find($item);
        $regularPrice = $currentItem->centPrice();
        $hasDiscount = !empty($discountcode);
        $taxAmount = 0;
        $price = 0;

        if (!$isInclusive && $hasDiscount) {
            $taxRate = $addTax / 100;
            $price = $regularPrice * (1 - $res->rate);
            $taxAmount = $regularPrice * $taxRate;
            $DiscountedTax = $price * $taxRate;
            $totalPrice = intval($price + $DiscountedTax);
            $isInclusive = false;
        } elseif ($isInclusive && $hasDiscount) {
            $taxRate = $includeTax / 100;
            $taxAmount = $regularPrice * $taxRate;
            $price = $regularPrice * (1 - $res->rate);
            $totalPrice = intval($price);
            $isInclusive = true;
        } elseif (!$isInclusive && !$hasDiscount) {
            $taxRate = $addTax / 100;
            $taxAmount = $regularPrice * $taxRate;
            $totalPrice = intval($regularPrice + $taxAmount);
            $isInclusive = false;
        } else {
            $taxRate = $includeTax / 100;
            $taxAmount = $regularPrice * $taxRate;
            $totalPrice = intval($regularPrice);
            $isInclusive = true;
        }

        $taxAmount = intval(round($taxAmount));

        $seriesItem = [
            'item_id' => $currentItem->id,
            'taxAmount' => $taxAmount,
            'isInclusive' => $isInclusive,
            'regularPrice' => $regularPrice,
            'afterDiscount' => intval($price),
            'totalPrice' => $totalPrice,
        ];

        $response = [];

        $updateParams = [
            'amount' => $seriesItem['totalPrice'],
        ];

        if ($paymentIntent) {
            $response = $this->paymentService->updateAmount($paymentIntent, $updateParams, $paymentMethod);
        }

        return response()->json([
            'calculation' => $seriesItem,
            'paymentIntent' => $response
        ]);
    }

}