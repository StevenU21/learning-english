<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class UnitResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        $unit = $this->resource;

        return [
            'id' => $unit->id,
            'slug' => $unit->slug,
            'level_id' => $unit->level_id,
            'name' => $unit->name,
            'description' => $unit->description,
            'expected_time' => (int) $unit->expected_time,
            'image_url' => $unit->image_url,
            'progress' => optional($unit->unitUserProgress->first())->progress ?? 0,
            'status' => optional($unit->unitUserProgress->first())->status ?? 'no_comenzado',
            'level' => [
                'id' => $unit->level->id,
                'name' => $unit->level->name,
                'slug' => $unit->level->slug,
            ],
        ];
    }
}
