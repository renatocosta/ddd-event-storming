<?php

namespace Application\UseCases\User\PaymentMethod;

use Common\ValueObjects\Identity\Guid;
use Common\ValueObjects\Misc\Email;
use Common\ValueObjects\Misc\Mobile;
use Domain\Model\User\IUserRepository;
use Domain\Model\User\UnableToHandleUser;
use Domain\Model\User\User;
use Domain\Model\User\UserEntityNotFound;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Infrastructure\Proxy\ProxyedBnB;

final class PaymentMethodUseCase implements IPaymentMethodUseCase
{

    public function __construct(public User $user, private IUserRepository $userRepository, private ProxyedBnB $proxy)
    {
    }

    public function execute(PaymentMethodInput $input): void
    {

        if (empty($input->customerId)) throw UnableToHandleUser::dueTo(UnableToHandleUser::USER_EMPTY_VALUE);
        $existingUser = $this->userRepository->getByCustomerAndUserId($input->customerId, Guid::from($input->userId));

        if ($existingUser instanceof UserEntityNotFound) throw new ModelNotFoundException(sprintf(UnableToHandleUser::CUSTOMER_NOT_FOUND, $input->customerId));
        $this->user->withCustomerId(Guid::from($existingUser->getIdentifier()), $existingUser->name(), new Mobile($existingUser->mobile()), new Email($existingUser->email()), '', $input->customerId);
        $this->user->updatePaymentMethod($input->paymentMethodToken);
    }

    public function proxy(): ProxyedBnB
    {
        return $this->proxy;
    }
}
