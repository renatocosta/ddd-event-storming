<?php

namespace Interfaces\Incoming\WebApi\Controllers;

use App\Http\Controllers\Controller;
use Application\UseCases\User\PaymentMethod\IPaymentMethodUseCase;
use Application\UseCases\User\PaymentMethod\PaymentMethodInput;
use Application\UseCases\User\Update\IUpdateUserUseCase;
use Application\UseCases\User\Update\UpdateUserInput;
use Domain\Model\User\IUserRepository;
use Infrastructure\Framework\Transformers\UserResource;
use Interfaces\Incoming\WebApi\Requests\UserUpdateMethodRequest;
use Interfaces\Incoming\WebApi\Requests\UserUpdateRequest;

class UserController extends Controller
{

    public function update(UserUpdateRequest $request, IUpdateUserUseCase $updateUserUseCase)
    {
        $updateUserUseCase->execute(new UpdateUserInput($request->get('order_id'), $request->get('mobile_number')));
        return (new UserResource($updateUserUseCase->user));
    }

    public function updatePaymentMethod(UserUpdateMethodRequest $request, IPaymentMethodUseCase $paymentMethodUseCase)
    {
        $paymentMethodUseCase->execute(new PaymentMethodInput(auth()->user()->customer_id, $request->get('payment_method_token'), auth()->user()->id));
        return response('', $paymentMethodUseCase->proxy()->response()->getStatusCode());
    }

    public function fetchAll(IUserRepository $userRepository)
    {
        return $userRepository->getAll();
    }
}
