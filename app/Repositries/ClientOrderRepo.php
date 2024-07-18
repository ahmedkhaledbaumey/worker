<?php

namespace App\Repositries;

use App\Interfaces\CrudRepoInterface;
use App\Models\ClientOrder;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\JsonResponse;

class ClientOrderRepo implements CrudRepoInterface
{
    public function store($request): JsonResponse
    {
        // التحقق مما إذا كان العميل قام بتسجيل الدخول بنجاح
        $clientId = Auth::guard('client')->id();
        if ($clientId === null) {
            return response()->json(['message' => 'Unauthorized access'], 401);
        }

        // التحقق مما إذا كانت هناك قيمة لـ post_id في الطلب
        if (!$request->has('post_id')) {
            return response()->json(['message' => 'Post ID is required'], 400);
        }

        // التحقق مما إذا كان الطلب موجود بالفعل
        $existingOrder = ClientOrder::where('client_id', $clientId)
            ->where('post_id', $request->post_id)
            ->exists();
        if ($existingOrder) {
            return response()->json(['message' => 'This service has already been added to your orders.'], 406);
        }

        // إنشاء الطلب الجديد
        $data = [
            'client_id' => $clientId,
            'post_id' => $request->post_id
        ];
        $order = ClientOrder::create($data);

        return response()->json(['message' => 'Order created successfully'], 200);
    }
}


// namespace App\Repositries;



// use App\Models\ClientOrder;
// use App\Interfaces\CrudRepoInterface;

// class ClientOrderRepo implements CrudRepoInterface
// {
//     public function store($request)
//     {
//         $clientId = auth()->guard('client')->id();

//         // Check if the client already has an order for the specified post
//         if (ClientOrder::where('client_id', $clientId)->where('post_id', $request->post_id)->exists()) {
//             return response()->json([
//                 'message' => 'This service has already been added to your orders.'
//             ], 406);
//         }

//         // If not, create a new order
//         $data = [
//             'client_id' => $clientId,
//             'post_id' => $request->post_id
//         ];

//         $order = ClientOrder::create($data);

//         return response()->json([
//             'message' => 'Order created successfully'
//         ], 200);
//     }
// }
