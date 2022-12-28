<?php

namespace Application\UseCases\Notification\Sms;

interface INotificationSmsPublishUseCase
{

    public function execute(NotificationSmsInput $input): void;
}
