<?php

namespace Infrastructure\Repositories;


final class CacheUtils
{

    //1 month
    public const TTL = 60 * 60 * 24 * 31;

    //Mobile_OrderNumber_Status
    public const PROJECT_REPORTS_KEY = 'project_reports_%s_%s';

    //Mobile_OrderNumber
    public const PROJECT_CURRENT_STATUS = 'project_current_status_%s';

    //OrderId
    public const PROJECT_REPORTED = 'project_reported_%s';

    //OrderNumber
    public const ORDER_KEY = 'order_%s';

    //UserId
    public const LEAD_USER = 'lead_user_%s';

    public const SUPPLIER_EXTERNALPARTNER_AUTH_ACCESS_TOKEN = 'supplier_externalpartner_auth_access_token';

    public const CLIENT_AUTH_EXPIRE_AT = 'client_auth_expire_at';
}
