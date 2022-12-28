<?php

namespace Application\UseCases\Notification\Email;

interface INotificationEmailNotifyUseCase
{

    public function execute(NotificationEmailInput $input): void;
}
