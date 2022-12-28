<?php

namespace Application\UseCases\User\Queries;

interface IAuthUserQuery
{
    public function execute(string $email, string $password): string;
}
