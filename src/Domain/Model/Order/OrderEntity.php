<?php

namespace Domain\Model\Order;

use Assert\Assert;
use Assert\AssertionFailedException;
use Common\ValueObjects\AggregateRoot;
use Common\ValueObjects\Identity\Identified;
use Common\ValueObjects\Identity\SmsVerificationCode;
use Common\ValueObjects\Misc\HumanCode;
use Common\ValueObjects\Misc\Mobile;
use Common\ValueObjects\Misc\Payload;
use Domain\Model\Order\Events\OrderConfirmed;
use Domain\Model\Order\Events\OrderCreated;
use Domain\Model\Order\Events\OrderUpdated;
use Domain\Model\Order\Events\ProjectAssignedToOrder;
use Domain\Model\Order\Events\ProjectReviewed;
use Domain\Model\Order\Events\RatingSent;
use Domain\Model\Order\Events\TipSent;
use Domain\Model\Order\State\OrderStateable;
use Domain\Model\ProjectReports\UnableToHandleProjectReports;

final class OrderEntity extends AggregateRoot implements Order
{

    private OrderStateable $state;

    private OrderStatus $status;

    private ?Identified $identifier = null;

    private int $customerId;

    private int $propertyId;

    private HumanCode $orderNumber;

    private Mobile $mobileNumber;

    private SmsVerificationCode $smsVerificationCode;

    private Payload $payload;

    private int $projectId = 0;

    private string $rating;

    private ?string $review;

    private bool $reviewed = false;

    private float $amount;

    private string $currency;

    public function fromState(OrderStateable $state): self
    {
        $this->state = $state;
        return $this;
    }

    public function create(): void
    {
        $this->status = $this->state->create();
        $this->raise(new OrderCreated($this));
    }

    public function uncreate(): void
    {
        $this->status = $this->state->uncreate();
    }

    public function confirm(): void
    {
        $this->status = $this->state->confirm();
        $this->raise(new OrderConfirmed($this));
    }

    public function update(): void
    {
        $this->status = $this->state->update();
        $this->raise(new OrderUpdated($this));
    }

    public function assignProject(int $projectId): void
    {
        try {
            Assert::that($projectId)->greaterThan(0);
        } catch (AssertionFailedException $e) {
            throw UnableToHandleProjectReports::dueTo($e->getMessage());
        }
        $this->projectId = $projectId;
        $this->raise(new ProjectAssignedToOrder($this));
    }

    public function markAsReviewed(): void
    {
        $this->reviewed = true;
        if ($this->getProjectId() == 0) {
            throw UnableToHandleProjectReports::dueTo(UnableToHandleOrders::INVALID_REVIEW);
        }
        $this->raise(new ProjectReviewed($this));
    }

    public function sendRating(string $rating, ?string $review): void
    {
        $this->rating = $rating;
        $this->review = $review;
        $this->raise(new RatingSent($this));
    }

    public function getRating(): string
    {
        return $this->rating;
    }

    public function getReview(): ?string
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
        $this->amount = $amount;
        $this->currency = $currency;
        $this->raise(new TipSent($this));
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
        $this->identifier = $identifier;
        $this->customerId = $customerId;
        $this->propertyId = $propertyId;
        $this->orderNumber = $orderNumber;
        $this->payload = $payload;
        $this->status = new OrderStatus(OrderStatus::UNCREATED);
        return $this;
    }

    public function from(HumanCode $orderNumber): self
    {
        $this->orderNumber = $orderNumber;
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
        return $this->identifier;
    }

    public function getProjectId(): int
    {
        return $this->projectId;
    }

    public function getCustomerId(): int
    {
        return $this->customerId;
    }

    public function getPropertyId(): int
    {
        return $this->propertyId;
    }

    public function reviewed(): bool
    {
        return $this->reviewed;
    }

    public function fromExisting(Identified $identifier, int $customerId, int $propertyId, OrderStatus $status, HumanCode $orderNumber, Payload $payload, Mobile $mobile, int $projectId = 0, bool $reviewed = false): self
    {
        $this->identifier = $identifier;
        $this->customerId = $customerId;
        $this->propertyId = $propertyId;
        $this->status = $status;
        $this->orderNumber = $orderNumber;
        $this->payload = $payload;
        $this->mobileNumber = $mobile;
        $this->projectId = $projectId;
        $this->reviewed = $reviewed;
        return $this;
    }

    public function withProjectId(Identified $identifier, OrderStatus $status, HumanCode $orderNumber, Payload $payload, Mobile $mobile, int $projectId): self
    {
        $this->identifier = $identifier;
        $this->status = $status;
        $this->orderNumber = $orderNumber;
        $this->payload = $payload;
        $this->mobileNumber = $mobile;
        $this->projectId = $projectId;
        return $this;
    }
}
