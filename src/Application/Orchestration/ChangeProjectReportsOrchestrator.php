<?php

namespace Application\Orchestration;

use Application\UseCases\Notification\Email\INotificationEmailPublishUseCase;
use Application\UseCases\Notification\Email\NotificationEmailInput;
use Application\UseCases\Notification\Email\NotificationEmailPublishUseCase;
use Application\UseCases\Notification\Sms\INotificationSmsPublishUseCase;
use Application\UseCases\Notification\Sms\NotificationSmsInput;
use Application\UseCases\Notification\Sms\NotificationSmsPublishUseCase;
use Application\UseCases\Order\AssignProject\AssignProjectInput;
use Application\UseCases\Order\AssignProject\AssignProjectUseCase;
use Application\UseCases\Order\AssignProject\IAssignProjectUseCase;
use Application\UseCases\ProjectReports\Change\ChangeProjectReportsUseCase;
use Application\UseCases\ProjectReports\Change\IChangeProjectReportsUseCase;
use Application\UseCases\User\AssignCustomer\AssignCustomerInput;
use Application\UseCases\User\AssignCustomer\AssignCustomerUseCase;
use Application\UseCases\User\AssignCustomer\IAssignCustomerUseCase;
use Application\UseCases\User\Queries\IGetUserCustomerQuery;
use Common\Application\Orchestration\UseCasesOrchestrator;
use Domain\Model\ProjectReports\MessageReport\MessageRecipientable;
use Domain\Model\ProjectReports\MessageReport\StepPart;
use Domain\Model\ProjectReports\ProjectReports;
use Illuminate\Database\DatabaseManager as DB;
use Domain\Services\OrderTrackable;
use Generator;

final class ChangeProjectReportsOrchestrator extends UseCasesOrchestrator
{

    private bool $messageConfiguratorEmailAccessible = false;

    private bool $messageConfiguratorSmsAccessible = false;

    public function __construct(
        private DB $db,
        private IChangeProjectReportsUseCase $changeProjectReportsUseCase,
        private INotificationEmailPublishUseCase $notificationEmailPublishUseCase,
        private INotificationSmsPublishUseCase $notificationSmsPublishUseCase,
        private IGetUserCustomerQuery $getUserCustomerQuery,
        private IAssignCustomerUseCase $assignCustomerUseCase,
        private IAssignProjectUseCase $assignProjectUseCase,
        private OrderTrackable $orderTrackable,
        public ProjectReports $projectReports,
        public MessageRecipientable $messageRecipient,
    ) {
    }

    protected function loadUseCases(mixed $initialInput): Generator
    {
        yield $this->changeProjectReportsUseCase => $initialInput;
        yield $this->getUserCustomerQuery => $this->orderTrackable->order->getCustomerId();
        yield $this->assignCustomerUseCase => $this->projectReports->shouldMapRelationships() ? new AssignCustomerInput($this->projectReports->getIdentifier()->id, $this->orderTrackable->order->getCustomerId()) : null;
        yield $this->assignProjectUseCase => $this->projectReports->shouldMapRelationships() ? new AssignProjectInput($this->projectReports->getIdentifier()->id, $this->statements['project_id']) : null;
        yield $this->notificationEmailPublishUseCase => $this->messageConfiguratorEmailAccessible ? new NotificationEmailInput($this->statements['sender_email_recipient'], $this->statements['sender_email_subject'], $this->statements['email_parameters']) : null;
        yield $this->notificationSmsPublishUseCase => $this->messageConfiguratorSmsAccessible ? new NotificationSmsInput($this->statements['sender_sms_recipient'], $this->statements['sms_text']) : null;
    }

    public function execute($initialInput): void
    {

        $this->db->transaction(function () use ($initialInput) {
            parent::execute($initialInput);
        });
    }

    protected function returnNextStatementFrom($useCase): array
    {

        switch ($useCase) {

            case $useCase instanceof ChangeProjectReportsUseCase:

                $resulSet =  ['sender_email_recipient' => '', 'sender_email_subject' => '', 'email_parameters' => '', 'sender_sms_recipient' => '', 'sms_text' => ['text' => '']];

                $data = $this->projectReports->payload()->asArray();
                $payload = $data['event_data'];

                if (in_array($data['event_type'], array_keys(StepPart::STEPS))) {
                    if (in_array('email', $this->messageRecipient->data->channels)) {
                        $this->messageConfiguratorEmailAccessible = true;
                    }
                    if (in_array('sms', $this->messageRecipient->data->channels)) {
                        $this->messageConfiguratorSmsAccessible = true;
                    }

                    $messageRecipient = $this->messageRecipient->result();

                    $resulSet =  ['sender_email_recipient' => $messageRecipient['sender']['email']['recipient'], 'sender_email_subject' => $messageRecipient['sender']['email']['subject'], 'email_parameters' => $messageRecipient['parameters'], 'sender_sms_recipient' => $messageRecipient['sender']['sms']['recipient'], 'sms_text' => ['text' => $messageRecipient['sender']['sms']['text']]];
                }

                if (isset($payload['customer']['id'])) {
                    $resulSet = array_merge($resulSet, ['customer_id' => $payload['customer']['id']]);
                }

                if (isset($payload['project']['id'])) {
                    $resulSet = array_merge($resulSet, ['project_id' => $payload['project']['id']]);
                }

                return $resulSet;

            case $useCase instanceof IGetUserCustomerQuery:
                $user = $this->getUserCustomerQuery->getUser();
                $this->projectReports->payload()->addAList([
                    'event_data' =>
                    [
                        'customer' =>
                        [
                            'user_id' => $user->getIdentifier()->id
                        ]
                    ]
                ]);
                return $this->statements;

            case $useCase instanceof AssignProjectUseCase:
                return array_merge($this->statements, [
                    'sender_sms_recipient' => $this->statements['sender_sms_recipient'], 'sms_text' => $this->statements['sms_text']
                ]);

            case $useCase instanceof AssignCustomerUseCase:
                return array_merge($this->statements, [
                    'sender_sms_recipient' => $this->statements['sender_sms_recipient'], 'sms_text' => $this->statements['sms_text']
                ]);

            case $useCase instanceof NotificationEmailPublishUseCase:
                return $this->statements;

            case $useCase instanceof NotificationSmsPublishUseCase:
                return $this->statements;

            default:
                return [];
        }
    }
}
