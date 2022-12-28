<?php

namespace Domain\Model\Order;

use Assert\Assertion;
use Assert\AssertionFailedException;
use Common\ValueObjects\ValueObject;

final class OrderStatus implements ValueObject
{

    public const UNCREATED = 0;

    public const CREATED = 1;

    public const CONFIRMED = 3;

    public const UPDATED = 4;

    public const STATUS = [self::UNCREATED => 'uncreated', self::CREATED => 'created', self::CONFIRMED => 'confirmed', self::UPDATED => 'updated'];

    public const CONFIRMED_MESSAGE = 'Offer sent to: %s';

    private string $status;

    public function __construct(private int $statusId)
    {
        try {
            Assertion::keyExists(self::STATUS, $statusId);
        } catch (AssertionFailedException $e) {
            throw new UnableToHandleOrders($e->getMessage());
        }

        $this->status = self::STATUS[$statusId];
    }

    public function isSame(ValueObject $statuses): bool
    {

        if (!$statuses instanceof self) {
            return false;
        }

        return $this->to() == $statuses->to();
    }

    public static function from(array $native): self
    {
        return new self($native['status']);
    }

    public function to(): array
    {
        return ['status' => $this->status];
    }

    public function getId(): int
    {
        return $this->statusId;
    }

    public function __toString(): string
    {
        return $this->status;
    }
}
