<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Tax;

class TaxController extends Controller
{
    public function retrieve($id)
    {
        $retrieveData = Tax::findOrFail($id);
        return response()->json([
            "message" => "Data retrieved successfully",
            "me" => $retrieveData,
            "status" => 200
        ]);
    }

    public function update(Request $request, $id) {

        // Retrieve incoming input
        $addtax_value = $request->boolean('addtax_value');
        $includetax_value = $request->boolean('includetax_value');

        // Updating data in database
        $updateData = Tax::find($id);
        $updateData->addtax_value = $addtax_value;
        $updateData->includetax_value = $includetax_value;

        // Check if all data to be store are not empty
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
