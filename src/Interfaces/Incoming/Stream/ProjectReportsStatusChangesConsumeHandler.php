<?php

namespace Interfaces\Incoming\Stream;

use Application\Orchestration\ChangeProjectReportsOrchestrator;
use Application\UseCases\ProjectReports\Change\ChangeProjectReportsInput;
use Application\UseCases\ProjectReports\Replicate\IReplicateProjectReportsUseCase;
use Application\UseCases\ProjectReports\Replicate\ReplicateProjectReportsInput;
use Common\ValueObjects\Misc\Payload;
use Interop\Queue\Context;
use Interop\Queue\Message;
use Ddd\BnbEnqueueClient\Contracts\EventHandlerInterface;
use Ddd\BnbSchemaRegistry\SchemaRegistry;

class ProjectReportsStatusChangesConsumeHandler implements EventHandlerInterface
{

    public function handle(Message $message, Context $context): void
    {

        $payload = new Payload($message->getBody());
        $payloadUnserialized = $payload->asArray();

        $schemaRegistry = (new SchemaRegistry($payloadUnserialized))
            ->readFrom($payloadUnserialized['event_type'])
            ->withException()
            ->build();

        $payloadFiltered = Payload::from($schemaRegistry->validated());

        if (!empty($message->getHeader('replay'))) {
            $replicateProjectReportsUseCase = app(IReplicateProjectReportsUseCase::class);
            $replicateProjectReportsUseCase->execute(new ReplicateProjectReportsInput($message->getHeader('correlation_id'), $payloadFiltered));
            return;
        }

        $changeProjectUseCase = app(ChangeProjectReportsOrchestrator::class);
        $changeProjectUseCase->execute(new ChangeProjectReportsInput($message->getHeader('correlation_id'), $payloadFiltered));
    }
}
