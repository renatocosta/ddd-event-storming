<?php

namespace Application\UseCases\Order\SendRating;

final class SendRatingInput
{

    public function __construct(public int $projectId, public int $rating, public ?string $review, public int $customerId)
    {
    }
}
