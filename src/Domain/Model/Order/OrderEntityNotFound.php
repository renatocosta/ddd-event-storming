<?php

namespace Domain\Model\Order;

use Common\ValueObjects\Identity\Identified;
use Common\ValueObjects\Identity\SmsVerificationCode;
use Common\ValueObjects\Misc\HumanCode;
use Common\ValueObjects\Misc\Mobile;
use Common\ValueObjects\Misc\Payload;
use Domain\Model\Order\State\OrderStateable;

final class OrderEntityNotFound implements Order
{

    private $order;

    public function fromState(OrderStateable $state): self
    {
        return $this;
    }

    public function create(): void
    {
    }

    public function uncreate(): void
    {
    }

    public function confirm(): void
    {
    }

    public function update(): void
    {
    }

    public function assignProject(int $projectId): void
    {
    }

    public function reviewed(): bool
    {
        return false;
    }

    public function markAsReviewed(): void
    {
    }

    public function sendRating(string $rating, ?string $review): void
    {
    }

    public function getRating(): string
    {
        return $this->rating;
    }

    public function getReview(): string
    {
        return $this->review;
    }
    public function getAmount(): float
    {
        return $this->amount;
    }

    public function getCurrency(): string
    {
        return $this->currency;
    }

    public function sendTip(float $amount, string $currency): void
    {
    }

    public function getStatus(): OrderStatus
    {
        return $this->status;
    }

    public function payload(): Payload
    {
        return $this->payload;
    }

    public function smsVerificationCode(): SmsVerificationCode
    {
        return $this->smsVerificationCode;
    }

    public function withSmsVerificationCode(SmsVerificationCode $smsVerificationCode): void
    {
        $this->smsVerificationCode = $smsVerificationCode;
    }

    public function of(Identified $identifier, int $customerId, int $propertyId, HumanCode $orderNumber, Payload $payload): self
    {
        return $this;
    }

    public function from(HumanCode $orderNumber): self
    {
        return $this;
    }

    public function orderNumber(): HumanCode
    {
        return $this->orderNumber;
    }

    public function mobileNumber(): Mobile
    {
        return $this->mobileNumber;
    }

    public function getIdentifier(): ?Identified
    {
        return null;
    }

    public function getProjectId(): int
    {
        return 0;
    }

    public function getCustomerId(): int
    {
        return 0;
    }

    public function getPropertyId(): int
    {
        return 0;
    }

    public function fromExisting(Identified $identifier, int $customerId, int $propertyId, OrderStatus $status, HumanCode $orderNumber, Payload $payload, Mobile $mobile, int $projectId = 0, bool $reviewed = false): self
    {
        return $this;
    }

    public function withProjectId(Identified $identifier, OrderStatus $status, HumanCode $orderNumber, Payload $payload, Mobile $mobile, int $projectId): self
    {
        return $this;
    }

    public function __toString(): string
    {
        return sprintf('Status %s', $this->status);
    }
}
