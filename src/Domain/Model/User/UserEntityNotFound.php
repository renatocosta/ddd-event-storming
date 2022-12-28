<?php

namespace Domain\Model\User;

use BadMethodCallException;
use Common\ValueObjects\Identity\Identified;
use Common\ValueObjects\Identity\Password;
use Common\ValueObjects\Identity\SmsVerificationCode;
use Common\ValueObjects\Misc\Email;
use Common\ValueObjects\Misc\Mobile;
use Infrastructure\Framework\Entities\UserModel;

final class UserEntityNotFound implements User
{

    public function identify(): void
    {
    }

    public function requestCode(): void
    {
    }

    public function authenticate(?UserModel $user): self
    {
        return $this;
    }

    public function authenticateWithOrderNumber(?UserModel $user): void
    {
    }

    public function setUserIdentified(UserModel $user): void
    {
    }

    public function update(?UserModel $user): void
    {
    }

    public function assignCustomer(): void
    {
    }

    public function userIdentified(): UserModel
    {
        throw new BadMethodCallException('Not implemented yet');
    }

    public function name(): string
    {
        throw new BadMethodCallException('Not implemented yet');
    }

    public function email(): Email
    {
        throw new BadMethodCallException('Not implemented yet');
    }

    public function mobile(): Mobile
    {
        throw new BadMethodCallException('Not implemented yet');
    }

    public function password(): Password
    {
        throw new BadMethodCallException('Not implemented yet');
    }

    public function smsVerificationCode(): SmsVerificationCode
    {
        throw new BadMethodCallException('Not implemented yet');
    }

    public function token(): string
    {
        throw new BadMethodCallException('Not implemented yet');
    }

    public function orderId(): string
    {
        throw new BadMethodCallException('Not implemented yet');
    }

    public function orderNumber(): string
    {
        throw new BadMethodCallException('Not implemented yet');
    }

    public function getCustomerId(): int
    {
        throw new BadMethodCallException('Not implemented yet');
    }

    public function getPaymentMethodToken(): string
    {
        throw new BadMethodCallException('Not implemented yet');
    }

    public function updatePaymentMethod(string $paymentMethodToken): void
    {
    }

    public function andFinally(): void
    {
        throw new BadMethodCallException('Not implemented yet');
    }

    public function getIdentifier(): ?Identified
    {
        throw new BadMethodCallException('Not implemented yet');
    }

    public function setIdentifier(Identified $id): void
    {
    }

    public function fromExisting(Mobile $mobile, SmsVerificationCode $smsVerificationCode, string $orderId, string $orderNumber): self
    {
        return $this;
    }

    public function from(Mobile $mobile, string $orderNumber): self
    {
        $this->mobile = $mobile;
        $this->orderNumber = $orderNumber;
        return $this;
    }

    public function of(Identified $identifier, string $name, Mobile $mobile, Email $email): self
    {
        return $this;
    }

    public function withOrderId(Identified $identifier, int $customerId, string $name, Mobile $mobile, Email $email, Password $password, string $orderId): self
    {
        return $this;
    }

    public function withCustomerId(Identified $identifier, string $name, Mobile $mobile, Email $email, string $orderId, int $customerId): self
    {
        return $this;
    }
}
