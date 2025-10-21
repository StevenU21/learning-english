<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class ExerciseResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        $exercise = $this->resource;

        return [
            'id' => $exercise->id,
            'prompt' => $exercise->prompt,
            'file_url' => $exercise->file_url,
            'file_b_url' => $exercise->file_b_url,
            'options' => $exercise->options,
            'exercise_type' => $exercise->exerciseType ? [
                'id' => $exercise->exerciseType->id,
                'name' => $exercise->exerciseType->name,
            ] : null,
            'lesson_id' => $exercise->lesson_id,
        ];
    }
}
