<?php

namespace App\Http\Controllers;

use App\Http\Resources\WorkerReviewsResource;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Requests\WorkerReviewRequest;
use App\Models\WorkerReviews;

class WorkerReviewController extends Controller
{
    public function store(WorkerReviewRequest $request)
    {
        if (!auth()->guard('client')->check()) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        $data = $request->all();
        $data['client_id'] = auth()->guard('client')->id();
        $reviews  = WorkerReviews::create($data);
        return response()->json([
            "data" => $reviews
        ]);
    }




    public function postRate($postId)
    {
        $averageRate = WorkerReviews::whereHas('post', function ($query) use ($postId) {
            $query->where('id', $postId);
        })
            ->avg('rate');

        $reviews = WorkerReviews::whereHas('post', function ($query) use ($postId) {
            $query->where('id', $postId);
        })
            ->get();

        return response()->json([
            'average_rate' => round($averageRate, 1),
            'data' => WorkerReviewsResource::collection($reviews)
        ]);
    }



    // public function postRate($post_id)
    // {
    //     $order = WorkerReviews::
    //         where('post_id', $reviewId)
    //         ->whereHas('post', function ($query) {
    //             $query->where('worker_id', auth()->guard('worker')->id());
    //         })
    //         ->first();

    //     return response()->json([
    //         "order" => $order
    //     ]);
    // }
}
