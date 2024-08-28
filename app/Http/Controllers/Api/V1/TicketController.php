<?php

namespace App\Http\Controllers\Api\V1;

use App\Models\Ticket;
use App\Http\Requests\Api\V1\StoreTicketRequest;
use App\Http\Requests\Api\V1\UpdateTicketRequest;
use App\Http\Resources\v1\TicketResource;
use App\Http\Filters\V1\TicketFilter;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use App\Models\User;

use function Laravel\Prompts\error;

class TicketController extends ApiController
{
    /**
     * Display a listing of the resource.
     */
    public function index(TicketFilter $filters)
    {
        return TicketResource::collection(Ticket::filter($filters)->paginate());

        /*Old method
        if($this->include('author')){
            return TicketResource::collection(Ticket::with('user')->paginate());
        }

        return TicketResource::collection(Ticket::paginate());*/

    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreTicketRequest $request)
    {
        try{
            $user = User::findOrFail($request->input('data.relationships.author.data.id'));
        }catch(ModelNotFoundException){
            return $this->ok('User not found',[
                    'error' => 'The provided user ID does not exists'
                ]
            );
        }

        $model = [
            'title' => $request->input('data.attributes.title'),
            'description' => $request->input('data.attributes.description'),
            'status' => $request->input('data.attributes.status'),
            'user_id' => $request->input('data.relationships.author.data.id'),
        ];

        return new TicketResource(Ticket::create($model));
    }

    /**
     * Display the specified resource.
     */
    public function show($ticket_id)
    {
        try{
            $ticket = Ticket::findOrFail($ticket_id);

            if($this->include('author')){
                return new TicketResource($ticket->load('user'));
            }

            return new TicketResource($ticket);
        }catch(ModelNotFoundException){
            return $this->error('Ticket not found', 404);
        }
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Ticket $ticket)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateTicketRequest $request, Ticket $ticket)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($ticket_id)
    {
        try{
            $ticket = Ticket::findOrFail($ticket_id);
            $ticket->delete();

            return $this->ok('Ticket deleted');
        }catch(ModelNotFoundException){
            return $this->error('Ticket not found', 404);
        }
    }
}
