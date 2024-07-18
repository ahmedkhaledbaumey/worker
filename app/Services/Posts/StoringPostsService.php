<?php

namespace App\Services\Posts;

use App\Models\Admin;
use Illuminate\Support\Facades\Notification;

use App\Models\Post;
use App\Models\post_photo;
use App\Notifications\AdminPost;
use Exception;
use Illuminate\Support\Facades\DB;

class StoringPostsService
{
    protected $model;

    public function __construct()
    {
        $this->model = new Post();
    }
    public function adminPercent($price)
    {
        $price = $price - ($price * 0.05);
        return $price;
    }

    function storePost($data)
    {
        $data = $data->except('photo');
        $data['price'] = $this->adminPercent($data['price']);
        $data['worker_id'] = auth()->guard('worker')->id();
        $post = Post::create($data);
        return $post;
    }

    public function storePostPhoto($request, $postId)
    {
        foreach ($request->file('photo') as $photo) {
            $postPhoto = new post_photo();
            $postPhoto->post_id = $postId;
            $postPhoto->photo = $photo->store('posts', 'public'); // تخزين الصورة بشكل عام في العامة
            $postPhoto->save(); // حفظ السجل الجديد
        }
    }

    public function sendAdminNotification($post)
    {
        $admins = Admin::get();
        $worker = auth()->guard('worker')->user();
        Notification::send($admins, new AdminPost($worker, $post));
    }

    public function store($request)
    {
        try {
            DB::beginTransaction();
            $post = $this->storePost($request);
            if ($request->hasFile('photo')) {
                $postPhoto = $this->storePostPhoto($request, $post->id);
            }
            $this->sendAdminNotification($post);
            DB::commit();
            return response()->json(['message' => 'post has been created , yor price after discount is ' . $post->price]);
        } catch (Exception $e) {
            DB::rollBack();
            return response()->json(['message' => $e->getMessage()], 500);
        }
    }
}
