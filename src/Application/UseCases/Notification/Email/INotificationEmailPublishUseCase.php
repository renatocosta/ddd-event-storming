<?php

namespace Application\UseCases\Notification\Email;

interface INotificationEmailPublishUseCase
{

    public function execute(NotificationEmailInput $input): void;
}
