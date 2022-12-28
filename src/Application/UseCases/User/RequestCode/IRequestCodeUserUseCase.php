<?php

namespace Application\UseCases\User\RequestCode;

interface IRequestCodeUserUseCase
{
    public function execute(RequestCodeUserInput $input): void;
}
