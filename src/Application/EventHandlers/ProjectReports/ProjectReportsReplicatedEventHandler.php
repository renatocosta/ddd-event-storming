<?php

namespace Application\EventHandlers\ProjectReports;

use Common\Application\Event\AbstractEvent;
use Common\Application\Event\DomainEventHandler;
use Domain\Model\ProjectReports\Events\ProjectReportsStatusReplicated;
use Domain\Model\ProjectReports\IProjectReportsRepository;

final class ProjectReportsReplicatedEventHandler implements DomainEventHandler
{

    public function __construct(private IProjectReportsRepository $projectReportsRepository)
    {
    }

    public function handle(AbstractEvent $domainEvent): void
    {
        $projectReports = $domainEvent->entity;
        $this->projectReportsRepository->create($projectReports);
    }

    public function isSubscribedTo(AbstractEvent $domainEvent): bool
    {
        return $domainEvent instanceof ProjectReportsStatusReplicated;
    }
}
