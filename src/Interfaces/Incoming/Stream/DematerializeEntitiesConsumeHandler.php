<?php

namespace Interfaces\Incoming\Stream;

use Domain\Model\ProjectReports\ProjectReportsStatus;
use Infrastructure\Framework\Entities\CleanersModel;
use Infrastructure\Framework\Entities\CleanersOrdersModel;
use Interop\Queue\Context;
use Interop\Queue\Message;
use Ddd\BnbEnqueueClient\Contracts\EventHandlerInterface;
use Illuminate\Support\Facades\DB;
use Infrastructure\Framework\Entities\OrderModel;
use Infrastructure\Framework\Entities\ProjectModel;

class DematerializeEntitiesConsumeHandler implements EventHandlerInterface
{

    private const ON_ORDER_CREATED = 'OrderCreated';

    private const ON_CUSTOMER_ADDING_MORE_CLEANERS = 'CustomerAddedMoreCleaners';

    private const ON_PROJECT_ORDER_ACCEPTED = ProjectReportsStatus::ORDER_ACCEPTED;

    private const ON_PROJECT_ORDER_FINISHED = ProjectReportsStatus::PROJECT_FINISHED;

    public function handle(Message $message, Context $context): void
    {
        $payload = json_decode($message->getBody(), true);
        $data = $payload['data']['event_data'];

        DB::transaction(function () use ($payload, $data) {

            if ($payload['event_type'] == self::ON_ORDER_CREATED) {
                $this->handleOrderCreated($data, $payload['order_id']);
            } elseif ($payload['event_type'] == self::ON_CUSTOMER_ADDING_MORE_CLEANERS) {
                $this->addCleaners($data['new_cleaners'], $payload['order_id']);
            } elseif ($payload['event_type'] == self::ON_PROJECT_ORDER_ACCEPTED) {
                $this->handleProjectOrderAccepted($data, $payload['order_id']);
            } elseif ($payload['event_type'] == self::ON_PROJECT_ORDER_FINISHED) {
                $this->handleProjectOrderFinished($data, $payload['order_id']);
            }
        });
    }

    private function handleOrderCreated(array $data, string $orderId): void
    {
        $this->addCleaners($data['cleaners'], $orderId);
    }

    private function handleProjectOrderAccepted(array $data, string $orderId): void
    {
        $project = $data['project'];
        $cleaner = $data['cleaner'];

        $projectData = ['start_date' => date('Y-m-d H:i', $project['start_date']), 'referenced_id' => $project['id']];
        if (isset($project['preferred_time'])) {
            $projectData = array_merge(['preferred_time_period' => $project['preferred_time']['period'], 'preferred_time_start_date' => date('Y-m-d H:i', $project['preferred_time']['start_date']), 'preferred_time_end_date' => date('Y-m-d H:i', $project['preferred_time']['end_date'])], $projectData);
        }

        ProjectModel::updateOrCreate(['referenced_id' => $project['id']], $projectData);

        $orderModel = OrderModel::findOrFail($orderId);

        OrderModel::where('id', $orderId)
            ->update(['cleaner_id' => $cleaner['id'], 'customer_id' => $orderModel->customer_id, 'property_id' => $orderModel->property_id]);
    }

    private function handleProjectOrderFinished(array $data, string $orderId): void
    {
        $project = $data['project'];
        ProjectModel::updateOrCreate(['referenced_id' => $project['id']], ['end_date' => date('Y-m-d H:i', $project['finished_at'])]);
    }

    private function addCleaners(array $cleaners, string $orderId): void
    {
        array_walk($cleaners, function (&$item, $key) {
            $item['referenced_id'] = $item['id'];
            unset($item['id']);
        });

        $cleanerOrders = [];

        foreach ($cleaners as $cleaner) {
            $cleanerResultSet = CleanersModel::updateOrCreate(
                ['referenced_id' => $cleaner['referenced_id']],
                ['name' => $cleaner['name'], 'referenced_id' => $cleaner['referenced_id']]
            );

            $cleanerOrders[] = [
                'cleaner_id' => $cleanerResultSet->id,
                'order_id' => $orderId
            ];
        }

        CleanersOrdersModel::upsert($cleanerOrders, ['order_id', 'cleaner_id'], ['order_id', 'cleaner_id']);
    }
}
