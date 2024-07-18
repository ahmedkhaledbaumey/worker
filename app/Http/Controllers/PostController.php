<?php

namespace App\Http\Controllers;

use App\Models\Post;
use App\Filters\PostFilter;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Spatie\QueryBuilder\QueryBuilder;
use App\Services\Posts\StoringPostsService;
use App\Http\Requests\posts\StoringPostsRequest;

class PostController extends Controller
{


    public function store(StoringPostsRequest $request)
    {
        try {
            DB::beginTransaction();

            $service = new StoringPostsService();
            $response = $service->store($request);

            DB::commit();

            return $response;
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['error' => 'An error occurred while storing the post: ' . $e->getMessage()], 500);
        }
    }

    public function allPost()
    {
        try {
            $posts = Post::all();

            return response()->json([
                'posts' => $posts
            ], 200);
        } catch (\Exception $e) {
            return response()->json(['error' => 'An error occurred while fetching all posts: ' . $e->getMessage()], 500);
        }
    }

    public function onePost($post_id)
    {
        try {
            $post = Post::find($post_id);

            if (!$post) {
                return response()->json(['error' => 'Post not found'], 404);
            }

            return response()->json([
                'post' => $post
            ], 200);
        } catch (\Exception $e) {
            return response()->json(['error' => 'An error occurred while fetching the post: ' . $e->getMessage()], 500);
        }
    }

    public function showApproved()
    {
        try { 
            $approvedPosts =  QueryBuilder::for(Post::class)
                ->allowedFilters((new PostFilter())->filters())
                ->with('worker:id,name')
                ->where('status', 'approve')  
                ->get(['id','content' , 'price','worker_id']); 
    
            return response()->json([
                'posts' => $approvedPosts
            ], 200);
        } catch (\Exception $e) {
            return response()->json(['error' => 'An error occurred while fetching approved posts: ' . $e->getMessage()], 500);
        }
    }
    
}
