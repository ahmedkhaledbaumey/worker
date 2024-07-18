<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class post_photo extends Model
{
    use HasFactory;

    protected $fillable = ['post_id', 'photo'];

// العلاقة بين جدول post_photos و posts
public function post(): BelongsTo
{
    return $this->belongsTo(Post::class);
}

}
