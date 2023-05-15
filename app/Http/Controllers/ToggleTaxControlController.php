<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\ToggleTaxControl;

class ToggleTaxControlController extends Controller
{
    public function retrieve($id)
    {
        $retrieveData = ToggleTaxControl::findOrFail($id);
        return response()->json([
            "message" => "Data retrieved successfully",
            "me" => $retrieveData,
            "status" => 200
        ]);
    }

    public function update(Request $request, $id) {

        // Retrieve incoming input
        $toggleControl1 = $request->input('toggleControl1');
        $toggleControl2 = $request->input('toggleControl2');

        // Updating data in database
        $updateData = ToggleTaxControl::find($id);
        $updateData->toggleControl1 = $toggleControl1;
        $updateData->toggleControl2 = $toggleControl2;

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
