<?php

namespace Application\UseCases\Order\Create;

interface ICreateOrderUseCase
{

    public function execute(CreateOrderInput $input): void;
}
