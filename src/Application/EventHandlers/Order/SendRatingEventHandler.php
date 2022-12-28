<?php

namespace Application\EventHandlers\Order;

use Application\UseCases\Order\ReviewProject\IReviewProjectUseCase;
use Application\UseCases\Order\ReviewProject\ReviewProjectInput;
use Common\Application\Event\AbstractEvent;
use Common\Application\Event\DomainEventHandler;
use Domain\Model\Order\Events\RatingSent;
use Infrastructure\Proxy\ProxyedBnB;
use Symfony\Component\HttpFoundation\Response;

final class SendRatingEventHandler implements DomainEventHandler
{

    public function __construct(private ProxyedBnB $proxy, private IReviewProjectUseCase $reviewProjectUseCase)
    {
    }

    public function handle(AbstractEvent $domainEvent): void
    {
        $order = $domainEvent->entity;
        $this->proxy->call('api/v1/ddd/sendRating', array_filter([
            'project_id' => $order->getProjectId(),
            'rating' => $order->getRating(),
            'review' => $order->getReview()
        ]));

        if ($this->proxy->response()->status() == Response::HTTP_OK) {
            $this->reviewProjectUseCase->execute(new ReviewProjectInput($order->getIdentifier()->id));
        }
    }

    public function isSubscribedTo(AbstractEvent $domainEvent): bool
    {
        return $domainEvent instanceof RatingSent;
    }
}
