<?php

namespace Domain\Model\User;

use Common\Exception\UnableToHandleException;

class UnableToHandleUser extends UnableToHandleException
{

    const MISMATCH_SMS_VERIFICATION_CODE = 'The provided SMS verification code %s or mobile number %s are incorrect.';

    const MISMATCH_ORDER_NUMBER = 'The provided order number %s or mobile number %s are incorrect.';

    const USER_NOT_FOUND = 'User %s not found';

    const CUSTOMER_NOT_FOUND = 'Customer %s not found';

    const RESEND_CODE_NOT_ALLOWED = 'Resend code not allowed for user id %s';

    const USER_EMPTY_VALUE = 'Customer ID can not be null';
}
