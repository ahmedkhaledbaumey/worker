<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class WorkerReviewsResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'post_id' => $this->post_id,
            'comment' => $this->comment,
            'rate' => $this->rate,

            // قم بإضافة الحقول الإضافية للعميل هنا
            'client' => [
                'id' => $this->client->id,
                'name' => $this->client->name,
                // إضافة المزيد من الحقول إذا لزم الأمر
            ],
        ];
    }
}
