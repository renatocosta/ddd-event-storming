<?php

namespace Interfaces\Incoming\Stream;

use Ddd\BnbEnqueueClient\Facades\Producer;
use Exception;
use Infrastructure\Repositories\LeadUsersRepository;
use Infrastructure\Proxy\CloseCrmProxy;
use Interop\Queue\Context;
use Interop\Queue\Message;
use Throwable;
use Ddd\BnbEnqueueClient\Contracts\EventHandlerInterface;

class ImportLeadToCrmConsumeHandler implements EventHandlerInterface
{

    public function handle(Message $message, Context $context): void
    {

        $payload = json_decode($message->getBody(), true);
        $leadUsersRepository = app(LeadUsersRepository::class);
        $hasActivity = isset($payload['activity_message']);

        $existingLeadUser = $leadUsersRepository->get($payload['user_id']);

        if ($message->getHeader('close_crm_lead_activity')) {
            $closeCrmLeadProxy = app(CloseCrmProxy::class);
            $closeCrmLeadProxy->call('activity/note/', ['timezone' => $payload['timezone'], 'note' => $payload['activity_message'], 'lead_id' => $existingLeadUser->lead_id]);
            return;
        }

        try {

            if (is_null($existingLeadUser)) {
                $this->create($payload, $hasActivity, $leadUsersRepository);
            } else {
                $this->update($payload, $hasActivity, $existingLeadUser);
            }
        } catch (Throwable $e) {
            throw new Exception($e->getMessage());
        }
    }

    private function update(array $payload, bool $hasActivity, $existingLeadUser)
    {
        $closeCrmLeadProxy = app(CloseCrmProxy::class);
        $kafkaProducer = app(Producer::class);

        //Just updating this existing lead user 
        //Avoiding duplicate contacts
        unset($payload['contacts']);
        $closeCrmLeadProxy->call('lead/' . $existingLeadUser->lead_id, $payload);
        if ($hasActivity) {
            $kafkaProducer::sendMessage('crm.leads.importlead', ['payload' => $payload, 'headers' => ['close_crm_lead_activity' => true]]);
        }
    }

    private function create(array $payload, bool $hasActivity, $leadUsersRepository)
    {
        $closeCrmLeadProxy = app(CloseCrmProxy::class);
        $kafkaProducer = app(Producer::class);

        //Assign this lead to the user related
        $closeCrmLeadProxy->call('lead/', $payload);
        $leadUsersRepository->create($payload['user_id'], $closeCrmLeadProxy->response()->json('id'));
        if ($hasActivity) {
            $kafkaProducer::sendMessage('crm.leads.importlead', ['payload' => $payload, 'headers' => ['close_crm_lead_activity' => true]]);
        }
    }
}
