<?php

namespace Application\UseCases\ProjectReports\Queries;

interface IGetProjectReportsStateQuery
{
    public function execute(string $orderNumber): array;
}
