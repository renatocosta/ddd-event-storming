<?php

namespace Application\UseCases\ProjectReports\Change;

use Common\ValueObjects\Identity\Guid;
use Common\ValueObjects\Misc\Payload;
use Domain\Model\ProjectReports\IProjectReportsRepository;
use Domain\Model\ProjectReports\ProjectReports;
use Domain\Model\ProjectReports\ProjectReportsStatus;
use Domain\Model\ProjectReports\State\ProjectReportsTransition;
use Domain\Services\OrderTrackable;

final class ChangeProjectReportsUseCase implements IChangeProjectReportsUseCase
{

    public function __construct(public ProjectReports $projectReports, private IProjectReportsRepository $projectReportsRepository, private OrderTrackable $orderTracker)
    {
    }

    public function execute(ChangeProjectReportsInput $input): void
    {

        $incomingPayload = new Payload($input->payload);
        $incomingPayloadUnserialized = $incomingPayload->asArray();
        $incomingProjectStatus = new ProjectReportsStatus($incomingPayloadUnserialized['event_type']);

        $order = $this->orderTracker->fetchCurrent(Guid::from($input->orderId));

        $projectResulSet = $this->projectReportsRepository->getStateByOrderNumber($order->orderNumber());

        $nextProjectStatus = (new ProjectReportsTransition($projectResulSet, $incomingProjectStatus))->nextStatus();

        $this->projectReports->from($order->getIdentifier(), $order->orderNumber(), $nextProjectStatus, $incomingPayload, $order->mobileNumber())
            ->match($incomingProjectStatus)
            ->change();
    }
}
