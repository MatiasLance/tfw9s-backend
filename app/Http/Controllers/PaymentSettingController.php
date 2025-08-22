<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\PaymentSetting;

class PaymentSettingController extends Controller
{
    public function list()
    {
        $response = PaymentSetting::latest()->first();
        return $response;
    }

    public function update(Request $request, $id)
    {
        $stripeEnabled = $request->boolean('stripeEnabled');
        $afterpayEnabled = $request->boolean('afterpayEnabled');

        $paymentSetting = PaymentSetting::find($id);
        $paymentSetting->stripe_enabled = $stripeEnabled;
        $paymentSetting->afterpay_enabled = $afterpayEnabled;
        $isSuccess = $paymentSetting->save();

        if($isSuccess){
            return response()->json(['success' => true, 'message' => 'Payment setting updated.'], 200);
        }else{
            return response()->json(['error' => true, 'message' => 'Payment setting failed.'], 500);
        }
    }
}
