<?php

namespace App\Http\Requests\Api\V1;

use App\Permissions\V1\Abilities;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

class StoreTicketRequest extends BaseTicketRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $user = Auth::user();

        $authorIdAtt = $this->routeIs('tickets.store') ? 'data.relationships.author.data.id' : 'author';

        $authorRule = 'required|integer|exists:users,id';

        $rules = [
            'data.attributes.title' => 'required|string',
            'data.attributes.description' => 'required|string',
            'data.attributes.status' => 'required|string|in:A,C,H,X',
            $authorIdAtt => $authorRule.'|size:'.$user->id
        ];

        if($user->tokenCan(Abilities::CreateTicket)){
            $rules[$authorIdAtt] .= '|'.$authorRule;
        }

        return $rules;
    }

    protected function prepareForValidation(){
        if($this->routeIs('authors.tickets.store')){
            $this->merge([
                'author' => $this->route('author')
            ]);
        }
    }

}
