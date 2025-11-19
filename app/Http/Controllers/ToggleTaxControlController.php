<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\ToggleTaxControl;

class ToggleTaxControlController extends Controller
{
    public function list()
    {
        $list = ToggleTaxControl::latest()->first();
        return $list;
    }

    public function update(Request $request, $id) {

        $toggleControl1 = $request->boolean('toggleControl1');
        $toggleControl2 = $request->boolean('toggleControl2');

        $updateData = ToggleTaxControl::find($id);
        $updateData->toggleControl1 = $toggleControl1;
        $updateData->toggleControl2 = $toggleControl2;

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
