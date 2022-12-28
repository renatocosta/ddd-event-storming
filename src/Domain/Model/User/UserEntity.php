<?php

namespace Domain\Model\User;

use Assert\Assert;
use Assert\AssertionFailedException;
use Common\ValueObjects\AggregateRoot;
use Common\ValueObjects\Identity\Identified;
use Domain\Model\User\Events\UserAuthenticated;
use Domain\Model\User\Events\UserIdentified;
use Illuminate\Support\Facades\Hash;
use Common\ValueObjects\Identity\Password;
use Common\ValueObjects\Identity\SmsVerificationCode;
use Common\ValueObjects\Misc\Email;
use Common\ValueObjects\Misc\Mobile;
use Domain\Model\User\Events\CustomerAndPaymentEnrolled;
use Domain\Model\User\Events\CustomerAssignedToUser;
use Domain\Model\User\Events\PaymentMethodUpdated;
use Domain\Model\User\Events\UserUpdated;
use Domain\Model\User\Events\UserVerificationCodeRequested;
use Domain\Model\User\Events\UserWithOrderNumberAuthenticated;
use Infrastructure\Framework\Entities\UserModel;

final class UserEntity extends AggregateRoot implements User
{

    private ?Identified $identifier = null;

    private string $name;

    private Mobile $mobile;

    private SmsVerificationCode $smsVerificationCode;

    private Email $email;

    private Password $password;

    private UserModel $userIdentified;

    private string $orderId;

    private int $customerId = 0;

    private string $orderNumber;

    private string $paymentMethodToken;

    private string $token;

    public function identify(): void
    {
        $this->raise(new UserIdentified($this));
        $this->raise(new CustomerAndPaymentEnrolled($this));
    }

    public function requestCode(): void
    {
        $this->raise(new UserVerificationCodeRequested($this));
    }

    public function authenticate(?UserModel $user): self
    {

        if (!$user || !Hash::check($this->smsVerificationCode->code(), $user->password)) {
            throw UnableToHandleUser::dueTo(UnableToHandleUser::MISMATCH_SMS_VERIFICATION_CODE, $this->smsVerificationCode->code(), $this->mobile());
        }
        $this->setUserIdentified($user);
        $this->raise(new UserAuthenticated($this));
        return $this;
    }

    public function authenticateWithOrderNumber(?UserModel $user): void
    {
        if (!$user || $user->customer?->order?->order_number != $this->orderNumber()) {
            throw UnableToHandleUser::dueTo(UnableToHandleUser::MISMATCH_ORDER_NUMBER, $this->orderNumber(), $this->mobile());
        }
        $this->setUserIdentified($user);
        $this->raise(new UserWithOrderNumberAuthenticated($this));
    }

    public function setUserIdentified(UserModel $user): void
    {
        $this->userIdentified = $user;
    }

    public function update(?UserModel $user): void
    {
        if (!$user) {
            throw UnableToHandleUser::dueTo(UnableToHandleUser::USER_NOT_FOUND, $this->getIdentifier());
        }
        $this->raise(new UserUpdated($this));
    }

    public function assignCustomer(): void
    {
        $this->raise(new CustomerAssignedToUser($this));
    }

    public function userIdentified(): UserModel
    {
        return $this->userIdentified;
    }

    public function name(): string
    {
        return $this->name;
    }

    public function email(): Email
    {
        return $this->email;
    }

    public function mobile(): Mobile
    {
        return $this->mobile;
    }

    public function password(): Password
    {
        return $this->password;
    }

    public function smsVerificationCode(): SmsVerificationCode
    {
        return $this->smsVerificationCode;
    }

    public function token(): string
    {
        return $this->token;
    }

    public function orderId(): string
    {
        return $this->orderId;
    }

    public function orderNumber(): string
    {
        return $this->orderNumber;
    }

    public function getCustomerId(): int
    {
        return $this->customerId;
    }

    public function getPaymentMethodToken(): string
    {
        return $this->paymentMethodToken;
    }

    public function updatePaymentMethod(string $paymentMethodToken): void
    {
        $this->paymentMethodToken = $paymentMethodToken;
        $this->raise(new PaymentMethodUpdated($this));
    }

    public function andFinally(): void
    {
        //Invalidate current access token
        $this->userIdentified->tokens()->delete();
        //Assign a new one with its abilities
        $this->token = $this->userIdentified->createToken('token_auth', (new UserAbilities(
            UserAbilities::MANAGE_ORDER,
            UserAbilities::MANAGE_PROJECT,
            UserAbilities::MANAGE_USER,
            UserAbilities::MANAGE_REVIEW,
            UserAbilities::SHARE_REPORT,
            UserAbilities::MANAGE_PAYMENT
        ))->abilities())->plainTextToken;
    }

    public function getIdentifier(): ?Identified
    {
        return $this->identifier;
    }

    public function setIdentifier(Identified $id): void
    {
        $this->identifier = $id;
    }

    public function fromExisting(Mobile $mobile, SmsVerificationCode $smsVerificationCode, string $orderId, string $orderNumber): self
    {
        $this->mobile = $mobile;
        $this->smsVerificationCode = $smsVerificationCode;
        $this->orderId = $orderId;
        $this->orderNumber = $orderNumber;
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
        try {
            Assert::that($name)->notEmpty()->maxLength(100);
        } catch (AssertionFailedException $e) {
            throw UnableToHandleUser::dueTo($e->getMessage());
        }
        $this->identifier = $identifier;
        $this->name = $name;
        $this->mobile = $mobile;
        $this->email = $email;
        return $this;
    }

    public function withOrderId(Identified $identifier, int $customerId, string $name, Mobile $mobile, Email $email, Password $password, string $orderId): self
    {
        try {
            Assert::that($name)->notEmpty()->maxLength(100);
            Assert::that($customerId)->greaterThan(0);
        } catch (AssertionFailedException $e) {
            throw UnableToHandleUser::dueTo($e->getMessage());
        }
        $this->identifier = $identifier;
        $this->customerId = $customerId;
        $this->name = $name;
        $this->mobile = $mobile;
        $this->email = $email;
        $this->password = $password;
        $this->orderId = $orderId;
        return $this;
    }

    public function withCustomerId(Identified $identifier, string $name, Mobile $mobile, Email $email, string $orderId, int $customerId): self
    {
        try {
            Assert::that($name)->notEmpty()->maxLength(100);
            Assert::that($customerId)->greaterThan(0);
        } catch (AssertionFailedException $e) {
            throw UnableToHandleUser::dueTo($e->getMessage());
        }
        $this->identifier = $identifier;
        $this->name = $name;
        $this->mobile = $mobile;
        $this->email = $email;
        $this->orderId = $orderId;
        $this->customerId = $customerId;
        return $this;
    }
}
