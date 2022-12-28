<?php

namespace Application\UseCases\Notification\Sms;

interface INotificationSmsNotifyUseCase
{

    public function execute(NotificationSmsInput $input): void;
}
