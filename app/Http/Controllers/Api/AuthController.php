<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\LoginUserRequest;
use App\Models\User;
use App\Traits\ApiResponses;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Permissions\V1\Abilities;

class AuthController extends Controller
{
    use ApiResponses;

    /**
     * Login
     *
     * Authenticas the user and returns API token.
     *
     * @unauthenticated
     * @group Authentication
     * @response 200 {
    "data": {
        "token": "{BEARER_TOKEN}"
    },
    "message": "Authenticated",
    "statusCode": 200
    }
     */

    public function login(LoginUserRequest $request){
        $request->validated($request->all());

        if(!Auth::attempt($request->only('email', 'password'))){
            return $this->error('Invalid Credentials', 401);
        }

        $user = User::firstWhere('email',$request->email);

        return $this->ok(
            'Authenticated',
            [
                'token' => $user->createToken(
                    'API Token for'. $user->email,
                    Abilities::getAbilities($user),
                    now()->addMonth()
                )->plainTextToken
            ]
        );
    }

    /**
     * Sign Out
     *
     * Signs out the current user.
     *
     * @group Authentication
     * @response 200 {}
     */

    public function logout(Request $request){
        //$request->user()->tokens()->delete(); //Revoke all tokens
        //$request->user()->tokens()->where('id', $token)->delete(); //If has ID
        $request->user()->currentAccessToken()->delete();

        return $this->ok('');

    }
}
