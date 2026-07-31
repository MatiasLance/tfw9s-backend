<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Series;
use App\Models\Team;
use App\Models\Tax;
use App\Models\DiscountCode;
use App\Models\ToggleTaxControl;
use App\Modules\IndividualRegistration\RegistrationIdentity;
use App\Modules\Payment\PaymentServiceInterface;
use App\Modules\Http\Message;
use App\Services\LoungeService;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class IndividualRegistrationController extends Controller
{
    /**
     * Payment service
     *
     * @var PaymentServiceInterface $paymentService
     */
    protected PaymentServiceInterface $paymentService;
    protected LoungeService $loungeService;

    public function __construct(
        PaymentServiceInterface $paymentService,
        LoungeService $loungeService
    )
    {
        $this->paymentService = $paymentService;
        $this->loungeService = $loungeService;
    }

    public function checkout(Request $request)
    {
        $validated = $request->validate([
            'item' => 'required|integer|exists:series,id',
            'payment_method' => ['required', 'string', Rule::in(['stripe', 'afterpay'])],
            'metadata' => 'nullable|array',
            'metadata.contactFirstName' => 'required|string|max:255',
            'metadata.contactLastName' => 'required|string|max:255',
            'metadata.contactPhoneNumber' => 'required|string|max:50',
            'metadata.contactEmail' => 'required|email|max:255',
            'metadata.playerFirstName' => 'required|string|max:255',
            'metadata.playerLastName' => 'required|string|max:255',
            'metadata.dob' => 'required|date_format:Y-m-d',
            'metadata.teamName' => 'required|integer|exists:teams,id',
            'metadata.ageGroup' => 'required|integer|exists:age_groups,id',
            'metadata.discountCodeId' => 'nullable|integer|min:0',
            'discountcode' => 'nullable|string',
            'lounge_token' => 'required|string',
            'client_id' => 'required|string|max:255',
        ]);

        $discountCodeId = (int) ($validated['metadata']['discountCodeId'] ?? 0);
        if ($discountCodeId > 0 && ! DiscountCode::whereKey($discountCodeId)->exists()) {
            throw ValidationException::withMessages([
                'metadata.discountCodeId' => 'The selected discount code is invalid.',
            ]);
        }

        $teamIsValid = Team::query()
            ->whereKey($validated['metadata']['teamName'])
            ->where('series_id', $validated['item'])
            ->where('agegroup_id', $validated['metadata']['ageGroup'])
            ->exists();

        if (! $teamIsValid) {
            throw ValidationException::withMessages([
                'metadata.teamName' => 'The selected team and age group do not belong to this Weekly Series.',
            ]);
        }

        if (!$this->loungeService->hasValidActiveSession(
            $validated['lounge_token'],
            $validated['client_id'],
            (int) $validated['item']
        )) {
            return response()->json([
                'success' => false,
                'message' => 'Your checkout session has expired. Please re-enter the lounge.'
            ], 409);
        }

        $photo = null;
        if ($request->hasFile('photo')) {
            $photo = [
                'file' => $request->file('photo'),
                'type' => 'photo'
            ];
        }

        $metadata = array_merge($validated['metadata'] ?? [], [
            'client_token' => $validated['client_id'],
        ]);
        $metadata['registration_key'] = RegistrationIdentity::make(
            (int) $validated['item'],
            $metadata
        );

        Log::info('Weekly Series checkout accepted', [
            'series_id' => (int) $validated['item'],
            'gateway' => $validated['payment_method'],
            'registration_key' => $metadata['registration_key'],
            'client_id_hash' => hash('sha256', $validated['client_id']),
        ]);

        return $this->paymentService->createIndividualRegistration(
            $validated['discountcode'] ?? null,
            $validated['payment_method'],
            $validated['item'],
            $metadata
        );
    }

    public function verify(Request $request, Message $message)
    {
        $validated = $request->validate([
            'transaction_id' => 'required|string|max:255',
            'payment_method' => ['required', 'string', Rule::in(['stripe', 'afterpay'])],
        ]);

        $paymentIntentId = $validated['transaction_id'];
        $paymentMethod = $validated['payment_method'];

        Log::info('Weekly Series verification started', [
            'transaction_id' => $paymentIntentId,
            'gateway' => $paymentMethod,
        ]);

        $status = $this->paymentService->verifyIndividualRegistration($paymentMethod, $paymentIntentId);

        Log::info('Weekly Series verification completed', [
            'transaction_id' => $paymentIntentId,
            'gateway' => $paymentMethod,
            'status' => $status->value,
        ]);

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

        $addTax = $tax->addTaxValue;
        $includeTax = $tax->includeTaxValue;
        $isInclusive = $master->toggleControl2;

        $taxAmount = 0;

        if($discountCodeID !== 0){
            $discountCode = DiscountCode::where('id', $discountCodeID)->first();
            $discountRate = floatval($discountCode->rate);

            if($this->hasDecimal($amount)){
                if (!$isInclusive && $discountRate != 0.0) {
                    $taxRate = $addTax / 100;
                    $price = $amount * (1 - $discountRate);
                    $taxAmount = $amount * $taxRate;
                    $totalPrice = floatval($price + $taxAmount);
                    $isInclusive = false;
                } elseif ($isInclusive && $discountRate != 0.0) {
                    $price = $amount * (1 - $discountRate);
                    $totalPrice = floatval($price);
                    $isInclusive = true;
                } elseif (!$isInclusive && $discountRate === 0.0) {
                    $taxRate = $addTax / 100;
                    $taxAmount = $amount * $taxRate;
                    $totalPrice = floatval($amount + $taxAmount);
                    $isInclusive = false;
                } else {
                    $totalPrice = floatval($amount);
                    $isInclusive = true;
                }

                return ['totalPrice' => $totalPrice];
            }else{
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

                return ['totalPrice' => $totalPrice];
                }
        }else{
            if($this->hasDecimal($amount)){
                if (!$isInclusive) {
                    $taxRate = $addTax / 100;
                    $price = $amount;
                    $taxAmount = $amount * $taxRate;
                    $totalPrice = floatval($price + $taxAmount);
                    $isInclusive = false;
                } elseif ($isInclusive) {
                    $totalPrice = floatval($amount);
                    $isInclusive = true;
                } elseif (!$isInclusive) {
                    $taxRate = $addTax / 100;
                    $taxAmount = $amount * $taxRate;
                    $totalPrice = floatval($amount + $taxAmount);
                    $isInclusive = false;
                } else {
                    $totalPrice = floatval($amount);
                    $isInclusive = true;
                }

                return ['totalPrice' => $totalPrice];
            }else{
                if (!$isInclusive) {
                    $taxRate = $addTax / 100;
                    $price = $amount;
                    $taxAmount = $amount * $taxRate;
                    $totalPrice = intval($price + $taxAmount);
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

                return ['totalPrice' => $totalPrice];
            }
        }
    }

    public function initialStripeCalculation(Request $request)
    {
        $item = $request->input('item');
        $amount = $request->input('amount');
        $discountCodeID = !is_null($request->input('discountID')) ? $request->input('discountID') : 0;
        
        $tax = Tax::find(1);
        $master = ToggleTaxControl::find(1);
        
        $addTax = $tax->addTaxValue;
        $includeTax = $tax->includeTaxValue;
        $isInclusive = $master->toggleControl2;

        $currentItem = Series::find($item);
        $regularPrice = $currentItem->centPrice();

        $taxAmount = 0;

        if($discountCodeID !== 0){
            $discountCode = DiscountCode::where('id', $discountCodeID)->first();
            $discountRate = floatval($discountCode->rate);

            if($this->hasDecimal($amount)){
                if (!$isInclusive && $discountRate != 0.0) {
                    $taxRate = $addTax / 100;
                    $price = $amount * (1 - $discountRate);
                    $taxAmount = $amount * $taxRate;
                    $totalPrice = floatval($price + $taxAmount);
                    $subTotal = floatval($totalPrice / 1.1);
                    $isInclusive = false;
                } elseif ($isInclusive && $discountRate != 0.0) {
                    $price = $amount * (1 - $discountRate);
                    $totalPrice = floatval($price);
                    $subTotal = floatval($totalPrice);
                    $isInclusive = true;
                } elseif (!$isInclusive && $discountRate === 0.0) {
                    $taxRate = $addTax / 100;
                    $taxAmount = $amount * $taxRate;
                    $totalPrice = floatval($amount + $taxAmount);
                    $subTotal = floatval($totalPrice / 1.1);
                    $isInclusive = false;
                } else {
                    $totalPrice = floatval($amount);
                    $subTotal = floatval($totalPrice);
                    $isInclusive = true;
                }

                $taxAmount = floatval(round($taxAmount));

                return response()->json([
                    'taxAmount' => $taxAmount,
                    'totalPrice' => $totalPrice * 100,
                    'subTotal' => $subTotal * 100
                ]);
            }else{
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
        }else{
            if($this->hasDecimal($amount)){
                if (!$isInclusive) {
                    $taxRate = $addTax / 100;
                    $price = $amount;
                    $taxAmount = $amount * $taxRate;
                    $totalPrice = floatval($price + $taxAmount);
                    $subTotal = floatval($totalPrice / 1.1);
                    $isInclusive = false;
                } elseif ($isInclusive) {
                    $price = $amount;
                    $totalPrice = floatval($price);
                    $subTotal = floatval($totalPrice);
                    $isInclusive = true;
                } elseif (!$isInclusive) {
                    $taxRate = $addTax / 100;
                    $taxAmount = $amount * $taxRate;
                    $totalPrice = floatval($amount + $taxAmount);
                    $subTotal = floatval($totalPrice / 1.1);
                    $isInclusive = false;
                } else {
                    $totalPrice = floatval($amount);
                    $subTotal = floatval($totalPrice);
                    $isInclusive = true;
                }

                $taxAmount = floatval(round($taxAmount));

                return response()->json([
                    'taxAmount' => $taxAmount,
                    'totalPrice' => $totalPrice * 100,
                    'subTotal' => $subTotal * 100
                ]);
            }else{
                if (!$isInclusive) {
                    $taxRate = $addTax / 100;
                    $price = $amount;
                    $taxAmount = $amount * $taxRate;
                    $totalPrice = intval($price + $taxAmount);
                    $subTotal = intval($totalPrice / 1.1);
                    $isInclusive = false;
                } elseif ($isInclusive) {
                    $price = $amount;
                    $totalPrice = intval($price);
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
    }

    protected function hasDecimal($value)
    {
        // https://www.php.net/manual/en/function.fmod.php
        return fmod((float)$value, 1) !== 0.0;
    }

}
