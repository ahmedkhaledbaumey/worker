<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Post extends Model
{
    use HasFactory;
    protected $fillable = ['worker_id', 'content', 'price', 'status', 'rejected_reason'];

    public function postPhotos(): HasMany
    {
        return $this->hasMany(post_photo::class);
    }

    // العلاقة بين جدول post و workers
    public function worker(): BelongsTo
    {
        return $this->belongsTo(Worker::class);
    }

}
