<?php

namespace Domain\Model\ProjectReports;

use Common\Repository\Repository;
use Common\ValueObjects\Identity\Identified;
use Common\ValueObjects\Misc\HumanCode;

interface IProjectReportsRepository extends Repository
{

    public function getBy(HumanCode $orderNumber, ProjectReportsStatus $status, array $filter = ['id', 'order_id', 'order_number', 'status', 'payload']): ProjectReports;

    public function getStateByOrderNumber(HumanCode $orderNumber): ProjectReports;

    public function getByStatus(Identified $identifier, ProjectReportsStatus $status, array $filter = ['id', 'order_id', 'order_number', 'status', 'payload']): ProjectReports;
}
