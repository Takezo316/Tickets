<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Filters\V1\AuthorFilter;
use App\Http\Requests\Api\V1\ReplaceUserRequest;
use App\Models\User;
use App\Http\Requests\Api\V1\StoreUserRequest;
use App\Http\Requests\Api\V1\UpdateUserRequest;
use App\Http\Resources\v1\UserResource;
use App\Policies\V1\UserPolicy;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class UserController extends ApiController
{
    protected $policyClass = UserPolicy::class;
    /**
     * Display a listing of the resource.
     */
    public function index(AuthorFilter $filters)
    {
        return UserResource::collection(
            User::filter($filters)->paginate()
        );
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreUserRequest $request)
    {
        //policy
        If($this->isAble('store', User::class)){
            return new UserResource(User::create($request->mappedAttributes()));
        }

        return $this->error('You cannot create', 401);
    }

    /**
     * Display the specified resource.
     */
    public function show(User $user)
    {
        if($this->include('users')){
            return new UserResource($user->load('users'));
        }

        return new UserResource($user);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateUserRequest $request, $user_id)
    {
        //PATCH
        try{
            $user = User::findOrFail($user_id);

            //policy
            if($this->isAble('update', $user)){
                $user->update($request->mappedAttributes());

                return new UserResource($user);
            }

            return $this->error('You cannot update', 401);

        }catch(ModelNotFoundException){
            return $this->error('User cannot be found', 404);
        }
    }

    public function replace(ReplaceUserRequest $request, $user_id)
    {
        //PUT
        try{

            $user = User::findOrFail($user_id);

            //policy
            if($this->isAble('replace', $user)){
                $user->update($request->mappedAttributes());

                return new UserResource($user);
            }

            return $this->error('You cannot replace', 401);

        }catch(ModelNotFoundException){

            return $this->error('User cannot be found', 404);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($user_id)
    {
        try{
            $user = User::findOrFail($user_id);
            //policy
            if($this->isAble('delete', $user)){
                $user->delete();

            return $this->ok('User deleted');
            }
            return $this->error('You cannot update', 401);
        }catch(ModelNotFoundException){
            return $this->error('User not found', 404);
        }
    }
}
