<?php

namespace App\Http\Controllers;

use App\Models\MasterShippingSetting;
use Illuminate\Http\Request;

class MasterShippingSettingController extends Controller
{
    //

    public function retrieve() {
        $data = MasterShippingSetting::all();
        return response()->json([
            "data" => $data
        ]);
    }

    public function store(Request $request) {
        $maxshipping_value = $request->input('maxshipping_value');
        $freeshipping_value = $request->input('freeshipping_value');

        $createData = MasterShippingSetting::create([
            "maxshipping_value" => $maxshipping_value, 
            "freeshipping_value" => $freeshipping_value
        ]);
        if($createData) {
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

    public function update(Request $request, $id) {
        $updateData = MasterShippingSetting::find($id);
        $maxshipping_value = $request->input('maxshipping_value');
        $freeshipping_value = $request->input('freeshipping_value');
        $updateData->maxshipping_value = $maxshipping_value;
        $updateData->freeshipping_value = $freeshipping_value;
        
        if($updateData) {
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

    public function delete($id) {
        $deleteData = MasterShippingSetting::find($id);
        if ($deleteData) {
            $deleteData->delete();
            return response()->json([
                "Message" => "Data successfully deleted",
                "Status" => 200
            ]);
        } else {
            return response()->json([
                "Message" => "Failed to delete data",
                "Status" => 400
            ]);
        }
    }
}
