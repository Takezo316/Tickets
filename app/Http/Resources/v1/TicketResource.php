<?php

namespace App\Http\Resources\v1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TicketResource extends JsonResource
{
    //public static $wrap = 'ticket';
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        //Payload Design
        return [
            'type' => 'ticket',
            'id' => $this->id,
            'attibutes' => [
                'title' => $this->title,
                'description' => $this->when(
                    $request->routeIs('tickets.show'),
                    $this->description
                ),
                'status' => $this->status,
                'createdAt' => $this->created_at,
                'updatedAt' => $this->updated_at
            ],
            'relationships' => [
                'author' => [
                    'data' => [
                        'type' => 'user',
                        'id' => $this->user_id,
                    ],
                    'links' => [
                        'self' => route('authors.show', ['author' => $this->user_id])
                    ]
                ]
            ],
            'includes' => new UserResource($this->whenLoaded('author')),
            'links' => [
                'self' => route('tickets.show', [$this->id])
            ]
        ];
    }
}
