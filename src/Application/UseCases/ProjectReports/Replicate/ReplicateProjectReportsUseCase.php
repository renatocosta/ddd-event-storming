<?php

namespace Application\UseCases\ProjectReports\Replicate;

use Common\ValueObjects\Identity\Guid;
use Common\ValueObjects\Misc\Payload;
use Domain\Model\ProjectReports\IProjectReportsRepository;
use Domain\Model\ProjectReports\ProjectReports;
use Domain\Model\ProjectReports\ProjectReportsStatus;
use Domain\Model\ProjectReports\State\ProjectReportsTransition;
use Domain\Services\OrderTrackable;

final class ReplicateProjectReportsUseCase implements IReplicateProjectReportsUseCase
{

    public function __construct(public ProjectReports $projectReports, private IProjectReportsRepository $projectReportsRepository, private OrderTrackable $orderTracker)
    {
    }

    public function execute(ReplicateProjectReportsInput $input): void
    {

        $incomingPayload = new Payload($input->payload);
        $incomingPayloadUnserialized = $incomingPayload->asArray();
        $incomingProjectReportsStatus = new ProjectReportsStatus($incomingPayloadUnserialized['event_type']);

        $order = $this->orderTracker->fetchCurrent(Guid::from($input->orderId));

        $projectReportsResulSet = $this->projectReportsRepository->getStateByOrderNumber($order->orderNumber());

        $nextProjectReportsStatus = (new ProjectReportsTransition($projectReportsResulSet, $incomingProjectReportsStatus))->nextStatus();

        $this->projectReports->from($order->getIdentifier(), $order->orderNumber(), $nextProjectReportsStatus, $incomingPayload, $order->mobileNumber())
            ->canBeReplicated()
            ->replicate();
    }
}
