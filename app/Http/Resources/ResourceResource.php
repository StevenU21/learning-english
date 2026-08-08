<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;

class ResourceResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  Request  $request
     * @return array
     */
    public function toArray($request)
    {
        $resource = $this->resource;

        return [
            'id' => $resource->id,
            'name' => $resource->name,
            'description' => $resource->description,
            'file_url' => Storage::url($resource->file_path),
            'unit_id' => $resource->unit_id,
        ];
    }
}
