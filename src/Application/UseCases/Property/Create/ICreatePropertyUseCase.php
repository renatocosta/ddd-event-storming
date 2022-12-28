<?php

namespace Application\UseCases\Property\Create;

interface ICreatePropertyUseCase
{

    public function execute(CreatePropertyInput $input): void;
}
