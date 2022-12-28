<?php

namespace Application\UseCases\Order\ReviewProject;

interface IReviewProjectUseCase
{

    public function execute(ReviewProjectInput $input): void;
}
