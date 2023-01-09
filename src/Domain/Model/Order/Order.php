<?php

namespace Domain\Model\Order;

use Common\ValueObjects\Identity\Identified;
use Common\ValueObjects\Identity\SmsVerificationCode;
use Common\ValueObjects\Misc\HumanCode;
use Common\ValueObjects\Misc\Mobile;
use Common\ValueObjects\Misc\Payload;
use Domain\Model\Order\State\OrderStateable;

interface Order
{

    public function fromState(OrderStateable $state): self;

    public function create(): void;

    public function uncreate(): void;

    public function confirm(): void;

    public function update(): void;

    public function assignProject(int $projectId): void;

    public function markAsReviewed(): void;

    public function sendRating(string $rating, ?string $review): void;

    public function getRating(): string;

    public function getReview(): ?string;

    public function getAmount(): float;

    public function getCurrency(): string;

    public function sendTip(float $amount, string $currency): void;

    public function getStatus(): OrderStatus;

    public function payload(): Payload;

    public function withSmsVerificationCode(SmsVerificationCode $smsVerificationCode): void;

    public function smsVerificationCode(): SmsVerificationCode;

    public function orderNumber(): HumanCode;

    public function mobileNumber(): Mobile;

    public function of(Identified $identifier, int $customerId, int $propertyId, HumanCode $orderNumber, Payload $payload): self;

    public function getIdentifier(): ?Identified;

    public function fromExisting(Identified $identifier, int $customerId, int $propertyId, OrderStatus $status, HumanCode $orderNumber, Payload $payload, Mobile $mobile, int $projectId = 0, bool $reviewed = false): self;

    public function getCustomerId(): int;

    public function getPropertyId(): int;

    public function getProjectId(): int;

    public function reviewed(): bool;
}
