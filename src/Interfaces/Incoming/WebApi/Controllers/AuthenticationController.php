<?php

namespace Interfaces\Incoming\WebApi\Controllers;

use App\Http\Controllers\Controller;
use Application\UseCases\User\AuthenticateWithOrderNumber\AuthenticateWithOrderNumberUserInput;
use Application\UseCases\User\AuthenticateWithOrderNumber\IAuthenticateWithOrderNumberUserUseCase;
use Application\UseCases\User\Queries\IAuthUserQuery;
use Illuminate\Http\Request;
use Infrastructure\Framework\Transformers\AuthenticationResource;
use Interfaces\Incoming\WebApi\Requests\AuthByOrderNumberRequest;

class AuthenticationController extends Controller
{

    public function authByOrderNumber(AuthByOrderNumberRequest $request, IAuthenticateWithOrderNumberUserUseCase $authenticateWithOrderNumberUserUseCase)
    {
        $authenticateWithOrderNumberUserUseCase->execute(new AuthenticateWithOrderNumberUserInput($request->get('email_mobile'), $request->get('order_number')));
        return (new AuthenticationResource($authenticateWithOrderNumberUserUseCase->user))->additional(['data' => ['token' => $authenticateWithOrderNumberUserUseCase->user->token()]]);
    }

    public function authByEmail(Request $request, IAuthUserQuery $query)
    {
        $userToken = $query->execute($request->get('email'), $request->get('password'));
        return response()->json([
            '_type'               => 'Authentication',
            'token'               => $userToken
        ]);
    }
}
