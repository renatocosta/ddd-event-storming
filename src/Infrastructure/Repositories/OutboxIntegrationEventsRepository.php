<?php

namespace Infrastructure\Repositories;

use Infrastructure\Framework\Entities\OrderIntegrationEventsModel;

final class OutboxIntegrationEventsRepository
{

    public function __construct(private OrderIntegrationEventsModel $model)
    {
    }

    public function readUnprocessed(): object|null
    {
        return $this->model->on('mysql::read')->whereNull('processed_at')->simplePaginate(5000);
    }

    public function create(array $item): void
    {
        $this->model->on('mysql::write');
        $this->model->create(['type' => $item['type'], 'type' => $item['type'], 'destination' => $item['destination'], 'data' => $item['data'], 'messageable_id' => $item['messageable_id']]);
    }

    public function updateAsProcessed(array $ids): void
    {
        $this->model
            ->on('mysql::write')
            ->whereIn('id', $ids)
            ->update([
                'processed_at' => now()
            ]);
    }
}
