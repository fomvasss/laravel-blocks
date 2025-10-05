<?php

namespace Fomvasss\Blocks\Http;

use Illuminate\Http\Resources\Json\JsonResource;

class BlockResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'name' => "*** {$this->name} ***", // for dashboard!
            'slug' => $this->slug,
            'type' => $this->type,
            'data' => $this->data ?: $this->content ?: null,
        ];
    }
}
