<?php

namespace Domain\Model\User;

use Assert\Assertion;
use Assert\AssertionFailedException;
use Common\ValueObjects\ValueObject;

final class UserAbilities implements ValueObject
{

    public const MANAGE_GLOBAL = 'manage-global';

    public const MANAGE_ORDER = 'manage-order';

    public const MANAGE_PROJECT = 'manage-project';

    public const MANAGE_USER = 'manage-user';

    public const MANAGE_REVIEW = 'manage-review';

    public const SHARE_REPORT = 'share-report';

    public const MANAGE_PAYMENT = 'manage-payment';

    public const ABILITIES_AVAILABLE = [self::MANAGE_GLOBAL, self::MANAGE_ORDER, self::MANAGE_PROJECT, self::MANAGE_USER, self::MANAGE_REVIEW, self::SHARE_REPORT, self::MANAGE_PAYMENT];

    private array $abilities;

    public function __construct(string ...$abilityOptions)
    {

        if (count($abilityOptions) == 0) throw new UnableToHandleUser('At least one ability required');

        try {
            foreach ($abilityOptions as $ability) {
                $this->abilities[] = $ability;
                Assertion::inArray($ability, self::ABILITIES_AVAILABLE);
            }
        } catch (AssertionFailedException $e) {
            throw new UnableToHandleUser($e->getMessage());
        }
    }

    public function isSame(ValueObject $abilities): bool
    {

        if (!$abilities instanceof self) {
            return false;
        }

        return $this == $abilities;
    }

    public function abilities(): array
    {
        return $this->abilities;
    }
}
