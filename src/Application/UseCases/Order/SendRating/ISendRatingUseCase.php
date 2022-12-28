<?php

namespace Application\UseCases\Order\SendRating;

use Infrastructure\Proxy\ProxyedBnB;

interface ISendRatingUseCase
{

    public function execute(SendRatingInput $input): void;

    public function proxy(): ProxyedBnB;
}
