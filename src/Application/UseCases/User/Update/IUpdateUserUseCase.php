<?php

namespace Application\UseCases\User\Update;

interface IUpdateUserUseCase
{

    public function execute(UpdateUserInput $input): void;
}
