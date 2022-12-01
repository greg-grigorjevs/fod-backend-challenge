<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class MovieResource extends JsonResource
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
            'name' => $this->name,
            'year' => $this->year,
            'genres' => GenreResource::collection($this->whenLoaded('genres')),
            'description' => $this->description,
            'imdbRating' => $this->when(isset($this->imdbRating), function () {
                return $this->imdbRating;
            })
        ];
    }
}
