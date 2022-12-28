<?php

namespace Domain\Model\User;

use Common\ValueObjects\Identity\Identified;
use Common\ValueObjects\Identity\Password;
use Common\ValueObjects\Identity\SmsVerificationCode;
use Common\ValueObjects\Misc\Email;
use Common\ValueObjects\Misc\Mobile;
use Infrastructure\Framework\Entities\UserModel;

interface User
{

    public function identify(): void;

    public function authenticate(?UserModel $user): self;

    public function authenticateWithOrderNumber(?UserModel $user): void;

    public function update(?UserModel $user): void;

    public function assignCustomer(): void;

    public function userIdentified(): UserModel;

    public function setUserIdentified(UserModel $user): void;

    public function requestCode(): void;

    public function name(): string;

    public function email(): Email;

    public function mobile(): Mobile;

    public function password(): Password;

    public function smsVerificationCode(): SmsVerificationCode;

    public function orderId(): string;

    public function orderNumber(): string;

    public function getCustomerId(): int;

    public function getPaymentMethodToken(): string;

    public function updatePaymentMethod(string $paymentMethodToken): void;

    public function token(): string;

    public function andFinally(): void;

    public function setIdentifier(Identified $id): void;

    public function getIdentifier(): ?Identified;

    public function fromExisting(Mobile $mobile, SmsVerificationCode $smsVerificationCode, string $orderId, string $orderNumber): self;

    public function from(Mobile $mobile, string $orderNumber): self;

    public function of(Identified $identifier, string $name, Mobile $mobile, Email $email): self;

    public function withOrderId(Identified $identifier, int $customerId, string $name, Mobile $mobile, Email $email, Password $password, string $orderId): self;

    public function withCustomerId(Identified $identifier, string $name, Mobile $mobile, Email $email, string $orderId, int $customerId): self;
}
