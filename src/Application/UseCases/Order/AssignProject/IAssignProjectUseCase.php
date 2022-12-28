<?php

namespace Application\UseCases\Order\AssignProject;

interface IAssignProjectUseCase
{

    public function execute(AssignProjectInput $input): void;
}
