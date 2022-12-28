<?php

namespace Interfaces\Incoming\Stream;

use Exception;
use Ddd\BnbEnqueueClient\Facades\Producer;
use Infrastructure\Repositories\AffiliateCustomerConversionsRepository;
use Infrastructure\Proxy\LinkMinkProxy;
use Interop\Queue\Context;
use Interop\Queue\Message;
use Throwable;
use Ddd\BnbEnqueueClient\Contracts\EventHandlerInterface;

class CreateConversionToAffiliateConsumeHandler implements EventHandlerInterface
{

    public function handle(Message $message, Context $context): void
    {
        $payload = json_decode($message->getBody(), true);
        $kafkaProducer = app(Producer::class);
        $affiliateCustomerConversionsRepository = app(AffiliateCustomerConversionsRepository::class);
        $linkMinkProxy = app(LinkMinkProxy::class);

        if ($message->getHeader('linkmink_commission')) {
            $linkMinkProxy->call('value-events', ['conversion_id' => $payload['conversion_id'], 'status' => 'pending', 'amount' => $payload['amount'], 'currency' => $payload['currency'], 'livemode' => env('LINKMINK_LIVEMODE')], env('LINKMINK_PRIVATE_LIVE_API_KEY'));
            return;
        }

        try {
            $linkMinkProxy->call('conversions', ['email' => $payload['email'], 'type' => env('LINKMINK_TYPE'), 'status' => env('LINKMINK_STATUS'), 'livemode' => env('LINKMINK_LIVEMODE'), 'lm_data' => $payload['affiliate_trackable_ref']], env('LINKMINK_PRIVATE_LIVE_API_KEY'));
            $affiliateCustomerConversionsRepository->create($payload['user_id'], $payload['order_id'], $payload['stripe_customer_id'], $linkMinkProxy->response()->json('conversion_id'));
            $kafkaProducer::sendMessage('marketing.affiliates.createconversion', ['payload' => ['conversion_id' => $linkMinkProxy->response()->json('conversion_id'), 'amount' => $payload['amount'], 'currency' => $payload['currency']], 'headers' => ['linkmink_commission' => true]]);
        } catch (Throwable $e) {
            throw new Exception($e->getMessage());
        }
    }
}
