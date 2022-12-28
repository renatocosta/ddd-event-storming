<?php

namespace Application\UseCases\Order\AddMoreCleaners;

interface IAddMoreCleanersUseCase
{

    public function execute(AddMoreCleanersInput $input): void;
}
