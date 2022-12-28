<?php

namespace Interfaces\Incoming\WebApi\Controllers;

use App\Http\Controllers\Controller;
use Application\UseCases\User\RequestCode\IRequestCodeUserUseCase;
use Application\UseCases\User\RequestCode\RequestCodeUserInput;
use Symfony\Component\HttpFoundation\Response;
use Interfaces\Incoming\WebApi\Requests\CodeResenderRequest;

class CodeResenderController extends Controller
{

    public function resend(CodeResenderRequest $request, IRequestCodeUserUseCase $requestCodeUserUseCase)
    {
        $requestCodeUserUseCase->execute(new RequestCodeUserInput($request->get('order_id')));
        return response('', Response::HTTP_NO_CONTENT);
    }
}
