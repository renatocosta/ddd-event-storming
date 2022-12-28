<?php

namespace Application\UseCases\ProjectReports\Change;

interface IChangeProjectReportsUseCase
{

    public function execute(ChangeProjectReportsInput $input): void;
}
