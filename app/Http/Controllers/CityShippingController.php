<?php

namespace App\Http\Controllers;

use App\Models\CityShipping;
use App\Modules\Shipping\CityShippingServiceInterface;
use App\Modules\Http\Message;
use Illuminate\Http\Request;

class CityShippingController extends Controller
{
    protected CityShippingServiceInterface $shippingService;

    public function __construct(CityShippingServiceInterface $shippingService)
    {
        $this->shippingService = $shippingService;
    }

    public function list() {
        $list = CityShipping::orderBy('created_at', 'desc')->simplePaginate(4);

        return response()->json([
            'list' => $list
        ]);
    }

    public function retrieve(Request $request, Message $message) {

        $data = $this->shippingService->retrieve();

        $message->setContent(200, 'Shipping data retrieved', '', [
            'data' => $data,
        ]);

        return $message->render();
    }

    public function store(Request $request, Message $message) {

        $name = $request->input('name');
        $city = $request->input('city');
        $shipping_value = $request->input('shipping_value');
        $insurance_value = $request->input('insurance_value');
        $registered_value = $request->input('registered_value');
        $express_value = $request->input('express_value');
            
        $data = $this->shippingService->create($name, $city, $shipping_value, $insurance_value, $registered_value, $express_value);
        if($data) {
            $message->setContent(201, "Data successfully save");
        }else{
            $message->setContent(400, 'Failed to save data');
        }

        return $message->render();
    }

    public function update(Request $request, Message $message, int $id) {

        $name = $request->input('name');
        $city = $request->input('city');
        $shipping_value = $request->input('shipping_value');
        $insurance_value = $request->input('insurance_value');
        $registered_value = $request->input('registered_value');
        $express_value = $request->input('express_value');

        $updateData = $this->shippingService->update($id ,$name, $city, $shipping_value, $insurance_value, $registered_value, $express_value);

        if ($updateData) {
            $message->setContent(200, 'Data successfully updated');
        } else {
            $message->setContent(400, 'Data not updated');
        }
            return $message->render();
        
    }

    public function delete(Request $request, Message $message, int $id) {

        $deleteData = $this->shippingService->delete($id);

        if ($deleteData) {
            $message->setContent(200, 'Data successfully deleted');
        } else {
            $message->setContent(400, 'Failed to delete data');
        }
            return $message->render();

    }
}
