<?php

namespace Domain\Model\ProjectReports;

use Assert\Assertion;
use Assert\AssertionFailedException;
use Common\ValueObjects\ValueObject;

final class ProjectReportsStatus implements ValueObject
{

    public const CONFIRMED = 'Confirmed';

    public const ORDER_ACCEPTED = 'OrderAccepted';

    public const PROJECT_STARTED = 'ProjectStarted';

    public const PROJECT_FINISHED = 'ProjectFinished';

    public const PROJECT_REPORTED = 'ProjectReported';

    public const PROJECT_PAYMENT_SUCCEEDED = 'PaymentSucceeded';

    public const PROJECT_PAYMENT_FAILED = 'PaymentFailed';

    public const PROJECT_CREDIT_CARD_AUTH_FAILED = 'CreditCardAuthFailed';

    public const PROJECT_CREDIT_CARD_AUTH_SUCCEDED = 'CreditCardAuthSucceded';

    public const PROJECT_CLEANING_COMING_UP_NOTIFIED = 'CleaningComingUpNotified';

    public const PROJECT_CLEANER_UPDATED_THE_CLEANING = 'CleanerUpdatedTheCleaning';

    public const PROJECT_CANCELLED  = 'ProjectCancelled';

    public const STATUS = [self::CONFIRMED => 0, self::ORDER_ACCEPTED => 1, self::PROJECT_STARTED => 2, self::PROJECT_FINISHED => 3, self::PROJECT_REPORTED => 4, self::PROJECT_PAYMENT_SUCCEEDED => 5, self::PROJECT_CREDIT_CARD_AUTH_FAILED => 6, self::PROJECT_CLEANING_COMING_UP_NOTIFIED => 7, self::PROJECT_CLEANER_UPDATED_THE_CLEANING => 8, self::PROJECT_CREDIT_CARD_AUTH_SUCCEDED => 9, self::PROJECT_CANCELLED => 10, self::PROJECT_PAYMENT_FAILED => 11];

    public const FOLLOW_UP_STATUS_ACTIVE = [self::STATUS[self::ORDER_ACCEPTED], self::STATUS[self::PROJECT_STARTED], self::STATUS[self::PROJECT_FINISHED], self::STATUS[self::PROJECT_CANCELLED]];

    public const TRACKING_STATUS = [self::ORDER_ACCEPTED, self::PROJECT_STARTED, self::PROJECT_FINISHED];

    public const FINAL_TRANSITION = [self::STATUS[self::PROJECT_FINISHED], self::STATUS[self::PROJECT_CANCELLED]];

    public const REPLICATION_STATUS_ALLOWED = [self::ORDER_ACCEPTED];

    public const CANCELLATION_REASONS = [
        'ProjectCancelled' => 'Cleaner Cancelled Project',
        'ProjectCancelledCreditCardAuthFailed' => 'Credit Card Auth Failed',
        'ProjectCancelledNoOneAcceptedTheOffer' => 'No Cleaner Accepted The Project'
    ];

    private int $statusId;

    public function __construct(private string $status)
    {
        try {
            Assertion::keyExists(self::STATUS, $status);
        } catch (AssertionFailedException $e) {
            throw UnableToHandleProjectReports::dueTo($e->getMessage());
        }

        $this->statusId = self::STATUS[$status];
    }

    public function isSame(ValueObject $statuses): bool
    {

        if (!$statuses instanceof self) {
            return false;
        }

        return $this->to() == $statuses->to();
    }

    public static function fromId(int $id): self
    {
        $st = array_search($id, self::STATUS);
        return new self($st);
    }

    public function previous(): string
    {
        $id = self::STATUS[$this->status] - 1;
        $st = array_search($id, self::STATUS);
        return $st;
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
