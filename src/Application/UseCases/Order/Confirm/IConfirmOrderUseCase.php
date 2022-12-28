<?php

namespace Application\UseCases\Order\Confirm;

interface IConfirmOrderUseCase
{

    public function execute(ConfirmOrderInput $input): void;
}
