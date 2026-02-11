<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Series;
use App\Models\Tax;
use App\Models\DiscountCode;
use App\Models\ToggleTaxControl;
use App\Modules\Payment\PaymentServiceInterface;
use App\Modules\Http\Message;
use App\Models\TeamLimit;
use App\Models\WaitingLounge;

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
        $validated = $request->validate([
            'item' => 'required|integer|exists:series,id',
            'payment_method' => 'required|string',
            'metadata' => 'nullable|array',
            'discountcode' => 'nullable|string',
            'lounge_token' => 'nullable|string',
            'client_id' => 'nullable|string'
        ]);

        try {
            $tokenData = decrypt($validated['lounge_token']);

            if ($tokenData['id'] !== $validated['client_id'] || $tokenData['exp'] < now()->timestamp) {
                return response()->json([
                  'success' => false,
                  'message' => 'Queue session expired. Please re-enter the lounge.'
                ]);
            }

            $hasActiveSlot = WaitingLounge::where('client_id', $validated['client_id'])
                ->where('series_id', $validated['item'])
                ->exists();

            if (!$hasActiveSlot) {
                return response()->json([
                  'success' => false,
                  'message' => 'Your checkout window has timed out.'
                ]);
            }

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid security token.'
            ]);
        }

        return $this->paymentService->createTeamRegistration(
            $validated['discountcode'] ?? null,
            $validated['payment_method'],
            $validated['item'],
            $validated['metadata'] ?? [],
            $validated['client_id']
        );
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

    /**
     * Determines if the lounge should be enforced.
     * We trigger this when spots are getting low (e.g., less than 5 left).
     */
    protected function isLoungeRequired($seriesId)
    {
        $limit = TeamLimit::where('series_id', $seriesId)->first();
        
        if (!$limit) return false;

        $remaining = $limit->team_limit - $limit->teamcount;

        return $remaining <= 5; 
    }
}
