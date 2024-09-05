<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Filters\V1\TicketFilter;
use App\Http\Requests\Api\V1\StoreTicketRequest;
use App\Http\Requests\Api\V1\ReplaceTicketRequest;
use App\Http\Requests\Api\V1\UpdateTicketRequest;
use App\Http\Resources\v1\TicketResource;
use App\Models\Ticket;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use App\Policies\V1\TicketPolicy;
use Illuminate\Http\Request;

class AuthorTicketsController extends ApiController
{
    protected $policyClass = TicketPolicy::class;

    public function index($author_id, TicketFilter $filters){
        return TicketResource::collection(
            Ticket::where('user_id', $author_id)
            ->filter($filters)
            ->paginate()
        );
    }

    public function store(StoreTicketRequest $request, $author_id){

        if($this->isAble('store', Ticket::class)){
            return new TicketResource(Ticket::create($request->mappedAttributes([
                'author' => 'user_id',
            ])));
        }

        return $this->error('You cannot create', 401);
    }

    public function update(UpdateTicketRequest $request, $author_id, $ticket_id)
    {
        //PATCH
        try{

            $ticket = Ticket::where('id',$ticket_id)
                        ->where('user_id', $author_id)
                        ->firstOrFail();

            if($this->isAble('update', $ticket)){
                $ticket->update($request->mappedAttributes());

                return new TicketResource($ticket);
            }
            return $this->error('You cannot update', 401);

        }catch(ModelNotFoundException){
            return $this->error('Ticket cannot be found', 404);
        }
    }

    public function replace(ReplaceTicketRequest $request, $author_id, $ticket_id)
    {
        //PUT
        try{
            $ticket = Ticket::where('id', $ticket_id)
                        ->where('user_id', $author_id)
                        ->firstOrFail();

            if($this->isAble('replace', $ticket)){

                $ticket->update($request->mappedAttributes());

                return new TicketResource($ticket);
            }
            return $this->error('You cannot replace', 401);

        }catch(ModelNotFoundException){

            return $this->error('Ticket cannot be found', 404);
        }
    }

    public function destroy($author_id, $ticket_id)
    {
        try{
            $ticket = Ticket::where('id',$ticket_id)
                        ->where('user_id', $author_id)
                        ->firstOrFail();

            if($this->isAble('delete', $ticket)){
                $ticket->delete();
                return $this->ok('Ticket deleted');
            }

            return $this->error('You cannot delete', 401);

        }catch(ModelNotFoundException $exception){
            return $this->error('Ticket not found', 404);
        }
    }
}
