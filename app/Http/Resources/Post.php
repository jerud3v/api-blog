<?php

namespace App\Http\Resources;

use Carbon\Carbon;
use Illuminate\Http\Resources\Json\JsonResource;

class Post extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param \Illuminate\Http\Request $request
     *
     * @return array
     */
    public function toArray($request)
    {
        $author = collect(\App\User::all())->firstWhere('id', $this->user_id);
        
        return [
            'data' => [
                'title' => $this->title,
                'content' => $this->content,
                'status' => $this->publish_status,
                'author' => $author->name,
                'create_at' => Carbon::parse($this->created_at)->format('d M Y H:i e'),
                'updated_at' => Carbon::parse($this->updated_at)->diffForHumans()
            ],
            'links' => [
                'self' => $this->path(),
            ],
        ];
    }
}
