<?php

namespace Application\UseCases\User\AuthenticateWithOrderNumber;

interface IAuthenticateWithOrderNumberUserUseCase
{

    public function execute(AuthenticateWithOrderNumberUserInput $input): void;
}
