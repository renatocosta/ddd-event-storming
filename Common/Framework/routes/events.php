<?php

use Illuminate\Support\Facades\Log;
use Interfaces\Incoming\Stream\CreateConversionToAffiliateConsumeHandler;
use Interfaces\Incoming\Stream\DematerializeEntitiesConsumeHandler;
use Interfaces\Incoming\Stream\ImportLeadToCrmConsumeHandler;
use Interfaces\Incoming\Stream\PostMessagesToSlackChannelConsumeHandler;
use Interfaces\Incoming\Stream\ProjectReportsStatusChangesConsumeHandler;
use Ddd\BnbEnqueueClient\Consumer\Consumer;
use Ddd\BnbEnqueueClient\Facades\Consumers;
use Interfaces\Incoming\Stream\SendSmsNotificationsConsumeHandler;
use Interfaces\Incoming\Stream\SendEmailNotificationsConsumeHandler;
use Interfaces\Incoming\Stream\TrackOrderToBusinessAnalyticsConsumeHandler;

Consumers::add("ddd.orders.smsnotifications", [
    'dlq' => 'ddd.orders.smsnotifications_dlq',
    'maxAttemps' => 2,
    'retryInterval' => 1000,
    'logger' => Log::channel('slackKafkaNotification')
], function (Consumer $consumer) {
    $consumer->addEventHandler("*", SendSmsNotificationsConsumeHandler::class);
});


Consumers::add("ddd.orders.emailnotifications", [
    'dlq' => 'ddd.orders.emailnotifications_dlq',
    'maxAttemps' => 2,
    'retryInterval' => 1000,
    'logger' => Log::channel('slackKafkaNotification')
], function (Consumer $consumer) {
    $consumer->addEventHandler("*", SendEmailNotificationsConsumeHandler::class);
});

Consumers::add("external-partner.project.statuschanges", [
    'dlq' => 'external-partner.project.statuschanges_dlq',
    'maxAttemps' => 2,
    'retryInterval' => 1000,
    'logger' => Log::channel('slackKafkaNotification')
], function (Consumer $consumer) {
    $consumer->addEventHandler("*", ProjectReportsStatusChangesConsumeHandler::class);
});

Consumers::add("crm.leads.importlead", [
    'dlq' => 'crm.leads.importlead_dlq',
    'maxAttemps' => 2,
    'retryInterval' => 1000,
    'logger' => Log::channel('slackKafkaNotification')
], function (Consumer $consumer) {
    $consumer->addEventHandler("*", ImportLeadToCrmConsumeHandler::class);
});

Consumers::add("workspace.channel.postmessages", [
    'dlq' => 'workspace.channel.postmessages_dlq',
    'maxAttemps' => 2,
    'retryInterval' => 1000,
    'logger' => Log::channel('slackKafkaNotification')
], function (Consumer $consumer) {
    $consumer->addEventHandler("*", PostMessagesToSlackChannelConsumeHandler::class);
});

Consumers::add("business.analytics.trackorder", [
    'dlq' => 'business.analytics.trackorder_dlq',
    'maxAttemps' => 2,
    'retryInterval' => 1000,
    'logger' => Log::channel('slackKafkaNotification')
], function (Consumer $consumer) {
    $consumer->addEventHandler("*", TrackOrderToBusinessAnalyticsConsumeHandler::class);
});

Consumers::add("marketing.affiliates.createconversion", [
    'dlq' => 'marketing.affiliates.createconversion_dlq',
    'maxAttemps' => 2,
    'retryInterval' => 1000,
    'logger' => Log::channel('slackKafkaNotification')
], function (Consumer $consumer) {
    $consumer->addEventHandler("*", CreateConversionToAffiliateConsumeHandler::class);
});

Consumers::add("general.purpose", [
    'dlq' => 'general.purpose_dlq',
    'maxAttemps' => 2,
    'retryInterval' => 1000,
    'logger' => Log::channel('slackKafkaNotification')
], function (Consumer $consumer) {
    $consumer->addEventHandler("*", DematerializeEntitiesConsumeHandler::class);
});
