<?php

namespace App\Http\Controllers;

use App\Models\Shipping;
use App\Modules\Shipping\ShippingServiceInterface;
use App\Modules\Http\Message;
use App\Modules\Shipping\CityShippingServiceInterface;
use App\Modules\Shipping\OtherCityShippingServiceInterface;
use App\Modules\Shipping\OtherCountryShippingServiceInterface;
use App\Modules\Shipping\OtherStateShippingServiceInterface;
use App\Modules\Shipping\StateShippingServiceInterface;

use Carbon\Carbon;
use Illuminate\Http\Request;

class ShippingController extends Controller
{

    protected ShippingServiceInterface $shippingService;

    public function __construct(ShippingServiceInterface $shippingService, StateShippingServiceInterface $stateShippingService,
    CityShippingServiceInterface $cityShippingService, OtherCountryShippingServiceInterface $otherCountryShippingService,
    OtherStateShippingServiceInterface $otherStateShippingService, OtherCityShippingServiceInterface $otherCityShippingService)
    {
        $this->shippingService = $shippingService;
        $this->stateShippingService = $stateShippingService;
        $this->cityShippingService = $cityShippingService;
        $this->otherCountryShippingService = $otherCountryShippingService;
        $this->otherStateShippingService = $otherStateShippingService;
        $this->otherCityShippingService = $otherCityShippingService;
    }

    public function list() {
        $list = Shipping::orderBy('created_at', 'desc')->simplePaginate(4);

        return response()->json([
            'list' => $list
        ]);
    }

    public function retrieve(Request $request, Message $message) {

        $shippingService = $this->shippingService->retrieve();
        $stateShippingService = $this->stateShippingService->retrieve();
        $cityShippingService = $this->cityShippingService->retrieve();
        $otherCountryShippingService = $this->otherCountryShippingService->retrieve();
        $otherStateShippingService = $this->otherStateShippingService->retrieve();
        $otherCityShippingService = $this->otherCityShippingService->retrieve();

        $shippingOptions = [$shippingService, $stateShippingService, $cityShippingService,
        $otherCountryShippingService, $otherStateShippingService, $otherCityShippingService];

            $message->setContent(200, 'Shipping data retrieved', '', [
                'data' => $shippingOptions,
            ]);

        return $message->render();
    }

    public function store(Request $request, Message $message) {

        $name = $request->input('name');
        $country = $request->input('country');
        $shipping_value = $request->input('shipping_value');
        $insurance_value = $request->input('insurance_value');
        $registered_value = $request->input('registered_value');
        $express_value = $request->input('express_value');
            
        $data = $this->shippingService->create($name, $country, $shipping_value, $insurance_value, $registered_value, $express_value);
        if($data) {
            $message->setContent(201, "Data successfully save");
        }else{
            $message->setContent(400, 'Failed to save data');
        }

        return $message->render();
    }

    public function update(Request $request, Message $message, int $id) {

        $name = $request->input('name');
        $country = $request->input('country');
        $shipping_value = $request->input('shipping_value');
        $insurance_value = $request->input('insurance_value');
        $registered_value = $request->input('registered_value');
        $express_value = $request->input('express_value');

        $updateData = $this->shippingService->update($id ,$name, $country, $shipping_value, $insurance_value, $registered_value, $express_value);

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
