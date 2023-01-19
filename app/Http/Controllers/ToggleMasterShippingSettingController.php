<?php

namespace App\Http\Controllers;

use App\Models\ToggleMasterShippingSetting;
use Illuminate\Http\Request;

class ToggleMasterShippingSettingController extends Controller
{
    public function retrieve() {
        $retrieveData = ToggleMasterShippingSetting::first();
        return response()->json([
            "data" => $retrieveData
        ]);
    }

    public function store(Request $request) {
        // Retrieving input data
        $togglemastersetting1 = $request->boolean('togglemastersetting1');
        $togglemastersetting2 = $request->boolean('togglemastersetting2');

        // Save to database
        $storeData = ToggleMasterShippingSetting::create([
            'togglemastersetting1' => $togglemastersetting1,
            'togglemastersetting2' => $togglemastersetting2,
        ]);

        // Check if all the data to be store are not empty
        if(!empty($storeData)) {
            return response()->json([
                "message" => "Data save successfully",
                "status" => 200
            ]);
        }else{
            return response()->json([
                "message" => "Data failed to save",
                "status" => 400
            ]);
        }
        
    }

    public function update(Request $request, $id) {

        // Retrieve incoming input
        $togglemastersetting1 = $request->boolean('togglemastersetting1');
        $togglemastersetting2 = $request->boolean('togglemastersetting2');

        // Updating data in database
        $updateData = ToggleMasterShippingSetting::find($id);
        $updateData->togglemastersetting1 = $togglemastersetting1;
        $updateData->togglemastersetting2 = $togglemastersetting2;

        // Check if all data to be store are not empty
        if(!empty($updateData)) {
            $updateData->save();
            return response()->json([
                "Message" => "Data successfully updated",
                "Status" => 200
            ]);
        }else{
            return response()->json([
                "Message" => "Data failed to update",
                "Status" => 400
            ]);
        }
    }
}
