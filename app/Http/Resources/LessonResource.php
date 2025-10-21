<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class LessonResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        $lesson = $this->resource;

        return [
            'id' => $lesson->id,
            'slug' => $lesson->slug,
            'unit_id' => $lesson->unit_id,
            'unit_slug' => optional($lesson->unit)->slug,
            'name' => $lesson->name,
            'duration' => (int) $lesson->duration,
            'description' => $lesson->description,
            'image_url' => $lesson->image_url,
            'progress' => optional($lesson->lessonUserProgress->first())->progress ?? 0,
            'status' => optional($lesson->lessonUserProgress->first())->status ?? 'no_comenzado',
        ];
    }
}
