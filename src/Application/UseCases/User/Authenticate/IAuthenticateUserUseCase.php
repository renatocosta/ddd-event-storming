<?php

namespace Application\UseCases\User\Authenticate;

interface IAuthenticateUserUseCase
{

    public function execute(AuthenticateUserInput $input): void;
}
