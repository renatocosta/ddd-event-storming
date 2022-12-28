<?php

namespace Application\Orchestration;

use Application\UseCases\Notification\Email\INotificationEmailPublishUseCase;
use Application\UseCases\Notification\Email\NotificationEmailInput;
use Application\UseCases\Notification\Email\NotificationEmailPublishUseCase;
use Application\UseCases\Notification\Sms\INotificationSmsPublishUseCase;
use Application\UseCases\Notification\Sms\NotificationSmsInput;
use Application\UseCases\Order\Confirm\ConfirmOrderUseCase;
use Application\UseCases\Order\Confirm\IConfirmOrderUseCase;
use Application\UseCases\User\Authenticate\AuthenticateUserInput;
use Application\UseCases\User\Authenticate\AuthenticateUserUseCase;
use Application\UseCases\User\Authenticate\IAuthenticateUserUseCase;
use Common\Application\Orchestration\UseCasesOrchestrator;
use Domain\Model\Notification\NotificationMessage;
use Illuminate\Database\DatabaseManager as DB;
use Domain\Model\Order\Order;
use Domain\Model\User\User;
use Generator;

final class ConfirmOrderOrchestrator extends UseCasesOrchestrator
{

    public function __construct(
        private DB $db,
        private IConfirmOrderUseCase $confirmOrderUseCase,
        private IAuthenticateUserUseCase $authenticateUserUseCase,
        private INotificationEmailPublishUseCase $notificationEmailPublishUseCase,
        private INotificationSmsPublishUseCase $notificationSmsPublishUseCase,
        public Order $order,
        public User $user
    ) {
    }

    protected function loadUseCases(mixed $initialInput): Generator
    {
        yield $this->confirmOrderUseCase => $initialInput;
        yield $this->authenticateUserUseCase => new AuthenticateUserInput($this->order->mobileNumber(), (string) $this->order->smsVerificationCode(), $this->order->getIdentifier()->id, $this->order->orderNumber());
        yield $this->notificationEmailPublishUseCase => new NotificationEmailInput($this->statements['user_model']->email, NotificationMessage::STEP1_EMAIL_SUBJECT_WAITING_FOR_CLEANERS, $this->statements['body']);
        yield $this->notificationSmsPublishUseCase => new NotificationSmsInput($this->statements['user_model']->mobile, $this->statements);
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

            case $useCase instanceof ConfirmOrderUseCase:
                return ['initial_payload' => $this->order->payload()->asArray()];

            case $useCase instanceof AuthenticateUserUseCase:

                $userModel = $this->user->userIdentified();
                $magicLink = sprintf(env('NOTIFICATIONS_MAGIC_LINK') . 'order/%s', rawurlencode(base64_encode($userModel->mobile . '/' . $this->user->orderNumber() . '/' . $this->user->smsVerificationCode())));
                    
                $this->order->payload()->addAList([
                    'event_data' =>
                    [
                        'customer' =>
                        [
                            'user_id' => $this->user->userIdentified()->id
                        ]
                    ]
                ]);

                return [
                    'user_model' => $userModel, 'magic_link' => $magicLink, 'sms_text' => sprintf(NotificationMessage::STEP1_SMS_WAITING_FOR_CLEANERS, ...[$this->user->orderNumber(), $magicLink]),
                    'body' => ['view_template' => 'order-confirmed', 'order_number' => $this->user->orderNumber(), 'user_name' => $userModel->name, 'magic_link' => $magicLink]
                ];

            case $useCase instanceof NotificationEmailPublishUseCase:
                return  ['user_model' => $this->statements['user_model'], 'text' => sprintf(NotificationMessage::STEP1_SMS_WAITING_FOR_CLEANERS, ...[$this->user->orderNumber(), $this->statements['magic_link']])];

            default:
                return [];
        }
    }
}
