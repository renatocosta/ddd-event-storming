<?php

namespace Domain\Model\Notification;

final class NotificationMessage
{

    const PHONE_NUMBER_VERIFICATION = '%s is your ddd phone verification code';

    const STEP1_EMAIL_SUBJECT_WAITING_FOR_CLEANERS = 'Cleaning request sent to Cleaners';

    const STEP1_SMS_WAITING_FOR_CLEANERS = 'Your ddd.com request was successfully sent to cleaners in your area. Track your cleaning status (%s) at %s';

    const STEP2_EMAIL_SUBJECT_ORDER_ACCEPTED = 'A cleaner accepted your request';

    const STEP2_SMS_ORDER_ACCEPTED = 'A cleaner accepted your ddd․com request. Track your cleaning status (%s) at %s';

    const STEP3_EMAIL_SUBJECT_PROJECT_STARTED = 'The Cleaning has started';

    const STEP3_SMS_PROJECT_STARTED = 'Your ddd․com Cleaner is at the property and the cleaning has started. Track your cleaning status (%s) at %s';

    const STEP4_EMAIL_SUBJECT_PROJECT_FINISHED = 'Cleaning Completed';

    const STEP4_SMS_PROJECT_FINISHED  = 'Your ddd.com Cleaner finished cleaning your property. Track your cleaning status (%s) at %s';

    const EMAIL_SUBJECT_PROJECT_REPORT = '%s has shared a cleaning report with you';

    const SMS_PROJECT_REPORT = '%s has shared a cleaning report with you, %s. Follow this link to see the details. %s';

    const EMAIL_SUBJECT_PROJECT_CREDIT_CARD_AUTH_FAILED = 'Credit Card Authorization Failed';

    const SMS_PROJECT_CREDIT_CARD_AUTH_FAILED = 'Your credit card authorization for your ddd.com Cleaning has failed. Change your credit card info at %s';

    const EMAIL_SUBJECT_PROJECT_CLEANING_COMING_UP_NOTIFIED = 'Your cleaning for %s is coming up';

    const SMS_PROJECT_CLEANING_COMING_UP_NOTIFIED = 'Your ddd․com cleaning for %s is coming up. Track your cleaning status (%s) at %s';

    const EMAIL_SUBJECT_PROJECT_CLEANER_UPDATED_THE_CLEANING = 'The Cleaner updated the Cleaning %s';

    const SMS_PROJECT_CLEANER_UPDATED_THE_CLEANING = 'Your ddd․com Cleaner updated the Cleaning %s. Track your cleaning status (%s) at %s';

    const EMAIL_SUBJECT_PROJECT_CREDIT_CARD_AUTH_SUCCEDED = 'Credit Card Authorization';

    const SMS_PROJECT_CREDIT_CARD_AUTH_SUCCEDED = 'We authorized your credit card for your upcoming ddd․com cleaning. You will not be charged until the cleaning is complete %s';

    const EMAIL_SUBJECT_PROJECT_CANCELED = 'The Cleaner has canceled the Cleaning';

    const SMS_PROJECT_CANCELED = 'Your ddd․com cleaner has canceled the cleaning. Track the status at %s or using the code %s';

    const EMAIL_SUBJECT_PROJECT_CANCELED_CREDIT_CARD_AUTH_FAILED = 'The Cleaning has been canceled';

    const SMS_PROJECT_CANCELED_CREDIT_CARD_AUTH_FAILED = 'Your ddd․com cleaning has been canceled because we were unable to complete the payment authorization on your credit card.';

    const EMAIL_SUBJECT_PROJECT_CANCELED_NO_ONE_ACCEPTED_THE_OFFER = 'Cleaning request sent to Cleaners';

    const SMS_PROJECT_CANCELED_NO_ONE_ACCEPTED_THE_OFFER = 'ddd.com has found no cleaners available in your area. Track your cleaning status (%s) at %s';
}
