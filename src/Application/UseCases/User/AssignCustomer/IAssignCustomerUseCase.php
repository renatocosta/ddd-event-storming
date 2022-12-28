<?php

namespace Application\UseCases\User\AssignCustomer;

interface IAssignCustomerUseCase
{

    public function execute(AssignCustomerInput $input): void;
}
