<?php

namespace Domain\Model\ProjectReports\Events;

use Common\Application\Event\AbstractEvent;
use Domain\Model\ProjectReports\ProjectReports;

class ProjectReportsStatusReplicated extends AbstractEvent
{

    public function __construct(public ProjectReports $entity)
    {
        parent::__construct($entity);
    }
}
