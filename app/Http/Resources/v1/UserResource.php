<?php

namespace App\Http\Resources\v1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'type' => 'user',
            'id' => $this->id,
            'attributes' => [
                'name' => $this->name,
                'email' => $this->email,
                //Form 1
                /*'emailVerifiedAt' => $this->when(
                    $request->routeIs('users.*'),
                    $this->email_verified_at
                )*/
                //Form 2
                $this->mergeWhen($request->routeIs('users.*'),[
                    'emailVerifiedAt' => $this->email_verified_at,
                    'createdAt' => $this->created_at,
                    'updatedAt' => $this->updated_at,
                ])
            ],
            'includes' => TicketResource::collection($this->whenLoaded('tickets')),
            'links' => [
                'self' => route('users.show', [$this->id])
            ]
        ];
    }
}
