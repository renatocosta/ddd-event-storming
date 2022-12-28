<?php

namespace Interfaces\Outgoing\BatchProcessing;

use Illuminate\Console\Command;
use Infrastructure\Repositories\OutboxIntegrationEventsRepository;
use Throwable;
use Ddd\BnbEnqueueClient\Facades\Producer;

class OutboxOrderEventService extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'outbox-order:dispatch';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Send all unprocessed messages to the outbox order integration topic';

    private array $destinationTopics = ['close' => 'crm.leads.importlead', 'workspace' => 'workspace.channel.postmessages', 'business_analytics' => 'business.analytics.trackorder', 'affiliate_conversions' => 'marketing.affiliates.createconversion', 'general_purpose' => 'general.purpose'];

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct(private Producer $kafkaProducer)
    {
        parent::__construct();
    }



    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle(OutboxIntegrationEventsRepository $outboxIntegrationEventsRepository)
    {

        $outboxEvents = $outboxIntegrationEventsRepository->readUnprocessed();

        $processedIds = [];

        $this->info('Total unprocessed: ' . count($outboxEvents));

        foreach ($outboxEvents as $item) {

            try {
                $payload = json_decode($item->data, true);
                $this->kafkaProducer::sendMessage($this->destinationTopics[$item->destination], ['payload' => $payload]);
                $processedIds[] = $item->id;
            } catch (Throwable $e) {
                $this->info('Err message: ' . $e->getMessage());
            }
        }

        $this->info('Total processed: ' . count($processedIds));

        if (count($processedIds) == 0) return;

        $outboxIntegrationEventsRepository->updateAsProcessed($processedIds);

        return 0;
    }
}
