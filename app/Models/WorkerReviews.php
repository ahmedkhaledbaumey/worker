<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WorkerReviews extends Model
{
    use HasFactory;
    protected $fillable = ['post_id', 'comment', 'rate', 'client_id'];
    public function client()
    {
        return $this->belongsTo(Client::class)->select('id', 'name');
    }
    public function post()
    {
        return $this->belongsTo(Post::class)->select('id', 'content');;
    }
}
