<?php

namespace Domain\Model\ProjectReports\MessageReport;

abstract class MessagePart
{

    public abstract function accept(MessageRecipient $messageRecipient): void;
}
