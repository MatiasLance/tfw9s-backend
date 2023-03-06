<?php

namespace App\Http\Controllers;

use App\Modules\Http\Message;
use App\Models\DiscountCode;
use Illuminate\Http\Request;
use App\Modules\Item\ItemServiceInterface;

class DiscountCodeController extends Controller
{
    /**
     * @var ItemServiceInterface $itemService
     */
    protected ItemServiceInterface $itemService;

    /**
     * DiscountCodeController constructor.
     * @param ItemServiceInterface $itemService
     */
    public function __construct(ItemServiceInterface $itemService)
    {
        $this->itemService = $itemService;
    }

    public function list(Request $request, Message $message) {
        $discountcode = $this->itemService->listDiscountCode();
        $total_discountcode = $this->itemService->countDiscountCode();

        $query = $request->query('q', null);
        $page = $request->query('page', null);

        $filter = [
            'q' => $query,
            'page' => $page
        ];

        $paginatedItems = $this->itemService->discountCodeItems($filter);

        $message->setContent(200, 'Discount Codes retrieved', '', [
            'discountcode' => $discountcode,
            'total_discountcode' => $total_discountcode,
            $paginatedItems->toArray()
        ]);

        return $message->render();
    }

    public function retrieve(Message $message, int $id) {
        $data = DiscountCode::findOrFail($id);
        $message->setContent(200, 'Discount code retrieved', '', [
            'discountcode' => $data
        ]);
        return $message->render();
    }

    public function discountCodeCheck(Request $request)
    {
        $inputCode = $request->input('code');
        $checkDiscountCode = DiscountCode::where('code', $inputCode)->exists();
        if ($checkDiscountCode) {
            $discountcode = DiscountCode::where('code', $inputCode)->first();
            return response()->json([
                'discountcode' => $discountcode,
                'isExist' => true,
                'message' => 'Discount code is valid'
            ]);
        } else {
            return response()->json([
                'discountcode' => [],
                'isExist' => false,
                'message' => 'Discount code is invalid'
            ]);
        }        
    }

    public function store(Request $request, Message $message)
    {
        $validate = $request->validate([
            'code' => 'required|max:20',
            'rate' => 'required',
            'description' => 'required|max:255',
            'amountapplied' => 'required'
        ]);
        $createData = DiscountCode::create($validate);
        if (!is_null($createData)) {
            return $message->successMessage();
        } else {
            return $message->errorMessage();
        }
    }

    public function update(Request $request, $id, Message $message)
    {
        $updateData = DiscountCode::findorFail($id);
        $validate = $request->validate([
            'code' => 'required|max:20',
        ]);
        $updateData->code = $validate['code'];
        $updateData->rate = $request->input('rate');
        $updateData->description = $request->input('description');
        $updateData->amountapplied = $request->input('amountapplied');
        $updateData->save();

        if (!is_null($validate)) {
            return $message->updateSuccessMessage();
        }else{
            return $message->updateErrorMessage();
        }
    }

    public function delete($id, Message $message)
    {
        $deleteData = DiscountCode::findorFail($id);
        if ($deleteData) {
            $deleteData->delete();
            return $message->deleteSuccessMessage();
        } else {
            return $message->deleteErrorMessage();
        }
    }
}
