<?php

namespace App\Http\Controllers\Api\V1;

use App\Models\Ticket;
use App\Http\Requests\Api\V1\StoreTicketRequest;
use App\Http\Requests\Api\V1\ReplaceTicketRequest;
use App\Http\Requests\Api\V1\UpdateTicketRequest;
use App\Http\Resources\v1\TicketResource;
use App\Http\Filters\V1\TicketFilter;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use App\Models\User;
use App\Policies\V1\TicketPolicy;
use Illuminate\Auth\Access\AuthorizationException;

use function Laravel\Prompts\error;

class TicketController extends ApiController
{
    protected $policyClass = TicketPolicy::class;
    /**
     * Get all tickets
     *
     * @group Managing Tickets
     * @queryParam sort string Data field(s) to srot by. Separate multiple  with commas. Denote descending sort with a minus. Example: sort=title,-createdAt
     */
    public function index(TicketFilter $filters)
    {
        return TicketResource::collection(Ticket::filter($filters)->paginate());
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreTicketRequest $request)
    {
        //policy
        if($this->isAble('store', Ticket::class)){
            return new TicketResource(Ticket::create($request->mappedAttributes()));
        }

        return $this->notAuthorized('You cannot create');
    }

    /**
     * Display the specified resource.
     */
    public function show($ticket_id)
    {
        $ticket = Ticket::findOrFail($ticket_id);

        if($this->include('author')){
            return new TicketResource($ticket->load('user'));
        }

        return new TicketResource($ticket);
    }

     /**
     * Update the specified resource in storage.
     */
    public function update(UpdateTicketRequest $request, Ticket $ticket)
    {
        //PATCH
        //policy
        if($this->isAble('update', $ticket)){
            $ticket->update($request->mappedAttributes());

            return new TicketResource($ticket);
        }

        return $this->notAuthorized('You cannot update');
    }

    public function replace(ReplaceTicketRequest $request, Ticket $ticket)
    {
        //PUT
        //policy
        if($this->isAble('replace', $ticket)){
            $ticket->update($request->mappedAttributes());

            return new TicketResource($ticket);
        }

        return $this->notAuthorized('You cannot replace');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Ticket $ticket)
    {
        //policy
        if($this->isAble('delete', $ticket)){
            $ticket->delete();

            return $this->ok('Ticket deleted');
        }
        return $this->notAuthorized('You cannot Delete');

    }
}
