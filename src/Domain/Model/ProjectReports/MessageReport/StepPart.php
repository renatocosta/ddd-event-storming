<?php

namespace Domain\Model\ProjectReports\MessageReport;

use Domain\Model\Notification\NotificationMessage;
use Assert\Assertion;
use Assert\AssertionFailedException;
use Domain\Model\ProjectReports\ProjectReportsStatus;
use Domain\Model\ProjectReports\UnableToHandleProjectReports;

final class StepPart extends MessagePart
{

    private array $step;

    private bool $selected = false;

    public const STEPS = [
        ProjectReportsStatus::ORDER_ACCEPTED => [
            'sender' =>
            [
                'email' => ['recipient' => '', 'subject' => NotificationMessage::STEP2_EMAIL_SUBJECT_ORDER_ACCEPTED],
                'sms' => ['recipient' => '', 'text' => NotificationMessage::STEP2_SMS_ORDER_ACCEPTED, 'text_params' => ['order_number', 'magic_link']]
            ],
            'parameters' => ['view_template' => 'order-accepted', 'order_number' => '', 'magic_link' => 'order/%s'],
            'origin_request' => 'cleaner'
        ],
        ProjectReportsStatus::PROJECT_STARTED => [
            'sender' =>
            [
                'email' => ['recipient' => '', 'subject' => NotificationMessage::STEP3_EMAIL_SUBJECT_PROJECT_STARTED],
                'sms' => ['recipient' => '', 'text' => NotificationMessage::STEP3_SMS_PROJECT_STARTED, 'text_params' => ['order_number', 'magic_link']]
            ],
            'parameters' => ['view_template' => 'project-started', 'user_name' => '', 'order_number' => '', 'magic_link' => 'order/%s'],
            'origin_request' => 'cleaner'
        ],
        ProjectReportsStatus::PROJECT_FINISHED => [
            'sender' =>
            [
                'email' => ['recipient' => '', 'subject' => NotificationMessage::STEP4_EMAIL_SUBJECT_PROJECT_FINISHED],
                'sms' => [
                    'recipient' => '', 'text' => NotificationMessage::STEP4_SMS_PROJECT_FINISHED, 'text_params' => ['order_number', 'magic_link']
                ]
            ],
            'parameters' => ['view_template' => 'project-finished', 'order_number' => '', 'magic_link' => 'order/%s'],
            'origin_request' => 'cleaner'
        ],
        ProjectReportsStatus::PROJECT_CREDIT_CARD_AUTH_FAILED => [
            'sender' =>
            [
                'email' => ['recipient' => '', 'subject' => NotificationMessage::EMAIL_SUBJECT_PROJECT_CREDIT_CARD_AUTH_FAILED],
                'sms' => [
                    'recipient' => '', 'text' => NotificationMessage::SMS_PROJECT_CREDIT_CARD_AUTH_FAILED, 'text_params' => ['magic_link']
                ]
            ],
            'parameters' => ['view_template' => 'project-credit-card-auth-failed', 'start_date' => '', 'magic_link' => 'order/%s/update-creditcard'],
            'date_format' => 'M d, Y',
            'origin_request' => 'cleaner'
        ],
        ProjectReportsStatus::PROJECT_CLEANING_COMING_UP_NOTIFIED => [
            'sender' =>
            [
                'email' => ['recipient' => '', 'subject' => NotificationMessage::EMAIL_SUBJECT_PROJECT_CLEANING_COMING_UP_NOTIFIED, 'subject_text_params' => ['start_date']],
                'sms' => [
                    'recipient' => '', 'text' => NotificationMessage::SMS_PROJECT_CLEANING_COMING_UP_NOTIFIED, 'text_params' => ['start_date', 'order_number', 'magic_link']
                ]
            ],
            'parameters' => ['view_template' => 'project-cleaning-coming-up-notified', 'start_date' => '', 'order_number' => '', 'magic_link' => 'order/%s'],
            'date_format' => 'M d, Y',
            'origin_request' => 'cleaner'
        ],
        ProjectReportsStatus::PROJECT_CLEANER_UPDATED_THE_CLEANING => [
            'sender' =>
            [
                'email' => ['recipient' => '', 'subject' => NotificationMessage::EMAIL_SUBJECT_PROJECT_CLEANER_UPDATED_THE_CLEANING, 'subject_text_params' => ['start_date']],
                'sms' => [
                    'recipient' => '', 'text' => NotificationMessage::SMS_PROJECT_CLEANER_UPDATED_THE_CLEANING, 'text_params' => ['start_date', 'order_number', 'magic_link']
                ]
            ],
            'parameters' => ['view_template' => 'project-cleaner-updated-the-cleaning', 'start_date' => '', 'order_number' => '', 'magic_link' => 'order/%s'],
            'date_format' => 'M d, Y, H:i',
            'origin_request' => 'cleaner'
        ],
        ProjectReportsStatus::PROJECT_CREDIT_CARD_AUTH_SUCCEDED => [
            'sender' =>
            [
                'email' => ['recipient' => '', 'subject' => NotificationMessage::EMAIL_SUBJECT_PROJECT_CREDIT_CARD_AUTH_SUCCEDED],
                'sms' => ['recipient' => '', 'text' => NotificationMessage::SMS_PROJECT_CREDIT_CARD_AUTH_SUCCEDED, 'text_params' => ['magic_link']]
            ],
            'parameters' => ['view_template' => 'project-credit-card-auth-succeded', 'user_name' => '', 'magic_link' => 'order/%s'],
            'origin_request' => 'cleaner'
        ],
        ProjectReportsStatus::PROJECT_CANCELLED => [
            'sender' =>
            [
                'email' => ['recipient' => '', 'subject' => NotificationMessage::EMAIL_SUBJECT_PROJECT_CANCELED],
                'sms' => ['recipient' => '', 'text' => NotificationMessage::SMS_PROJECT_CANCELED, 'text_params' => ['magic_link', 'order_number']]
            ],
            'parameters' => ['view_template' => 'project-cancelled', 'order_number' => '', 'magic_link' => 'order/%s'],
            'origin_request' => 'cleaner',
            'sub_step' => [
                'ProjectCancelledCreditCardAuthFailed' => [
                    'sender' =>
                    [
                        'email' => ['recipient' => '', 'subject' => NotificationMessage::EMAIL_SUBJECT_PROJECT_CANCELED_CREDIT_CARD_AUTH_FAILED],
                        'sms' => ['recipient' => '', 'text' => NotificationMessage::SMS_PROJECT_CANCELED_CREDIT_CARD_AUTH_FAILED, 'text_params' => []]
                    ],
                    'parameters' => ['view_template' => 'project-cancelled-credit-card-auth-failed', 'cancellation_date' => ''],
                    'date_format' => 'M d, Y, \a\t h:i A',
                    'origin_request' => 'cleaner'
                ],
                'ProjectCancelledNoOneAcceptedTheOffer' => [
                    'sender' =>
                    [
                        'email' => ['recipient' => '', 'subject' => NotificationMessage::EMAIL_SUBJECT_PROJECT_CANCELED_NO_ONE_ACCEPTED_THE_OFFER],
                        'sms' => ['recipient' => '', 'text' => NotificationMessage::SMS_PROJECT_CANCELED_NO_ONE_ACCEPTED_THE_OFFER, 'text_params' => ['order_number', 'magic_link']]
                    ],
                    'parameters' => ['view_template' => 'project-cancelled-no-one-accepted-the-offer', 'user_name' => '', 'magic_link' => 'order/%s', 'order_number' => ''],
                    'date_format' => 'M d, Y, \a\t h:i A',
                    'origin_request' => 'cleaner'
                ]
            ]
        ],
        ProjectReportsStatus::PROJECT_REPORTED => [
            'sender' =>
            [
                'email' => ['recipient' => '', 'subject' => NotificationMessage::EMAIL_SUBJECT_PROJECT_REPORT, 'subject_text_params' => ['user_name']],
                'sms' => [
                    'recipient' => '', 'text' => NotificationMessage::SMS_PROJECT_REPORT, 'text_params' => ['user_name', 'receiver_name', 'magic_link']
                ]
            ],
            'parameters' => ['view_template' => 'project-reported', 'user_name' => '', 'receiver_name' => '', 'report_text' => '', 'magic_link' => 'order/%s/report'],
            'origin_request' => 'request'
        ]
    ];


    public function __construct(private string $stepName, private array $payload)
    {
        try {
            Assertion::keyExists(self::STEPS, $this->stepName);
        } catch (AssertionFailedException $e) {
            throw UnableToHandleProjectReports::dueTo($e->getMessage());
        }

        $this->step = self::STEPS[$this->stepName];

        if ($stepName == ProjectReportsStatus::PROJECT_CANCELLED && isset($this->payload['event_data']['project']['cancellation_reason'])) {
            $reason = $this->payload['event_data']['project']['cancellation_reason'];
            if ($this->hasSub($reason)) {
                $this->step = $this->readFromSub($reason);
            }
        }

        $this->selected = true;
    }

    private function hasSub(string $sub): bool
    {

        if (empty($sub)) return false;

        return isset($this->step['sub_step'][$sub]);
    }

    private function readFromSub(string $sub): array
    {
        return $this->step['sub_step'][$sub];
    }

    public function selection(): array
    {
        return $this->step;
    }

    public function accept(MessageRecipient $messageRecipient): void
    {
        $messageRecipient->visitStep($this);
    }

    public function isSelected(): bool
    {
        return $this->selected;
    }

    public function __toString(): string
    {
        return $this->stepName;
    }
}
