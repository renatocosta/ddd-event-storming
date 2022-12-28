<?php

namespace Application\UseCases\User\Identify;

interface IIdentifyUserUseCase
{

    public function execute(IdentifyUserInput $input): void;
}
