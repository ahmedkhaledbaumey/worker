<?php

namespace App\Http\Controllers\AdminDashboard;

use Exception;
use App\Models\Post;
use Illuminate\Http\Request;
use App\Notifications\AdminPost;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Notification;
use App\Http\Requests\posts\PostStatusRequest;

class PostStatusController extends Controller
{
    public function changeStatus(PostStatusRequest $request)
    {
        try {
            // بداية العملية
            DB::beginTransaction();

            // التحقق من وجود المنشور
            $post = Post::find($request->post_id);
            if (!$post) {
                return response()->json(['error' => 'Post not found'], 404);
            }

            // تحديث حالة المنشور
            $post->update([
                "status" => $request->status,
                "rejected_reason" => $request->rejected_reason,
            ]);

            // تأكيد العملية
            DB::commit();

            // إرسال إشعار للعامل
            Notification::send($post->worker, new AdminPost($post->worker, $post));

            // رسالة الاستجابة بنجاح
            return response()->json([
                "message" => "Post status has been changed"
            ], 200);
        } catch (Exception $e) {
            // في حالة حدوث أي خطأ، يتم التراجع عن العملية وإرجاع رسالة خطأ
            DB::rollBack();
            return response()->json(['error' => 'An error occurred while changing post status: ' . $e->getMessage()], 500);
        }
    }
}
