<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Tax;

class TaxController extends Controller
{
    public function list()
    {
        $list = Tax::latest()->first();
        return $list;
    }

    public function update(Request $request, $id)
    {
        $addTaxValue = $request->input('addTaxOnCartPrice');

        $updateData = Tax::find($id);
        $updateData->addTaxValue = $addTaxValue;

        if(!empty($updateData)) {
            $updateData->save();
            return response()->json([
                "message" => "Data successfully updated",
                "status" => 200
            ]);
        }else{
            return response()->json([
                "message" => "Data failed to update.",
                "status" => 400
            ]);
        }
    }
}
