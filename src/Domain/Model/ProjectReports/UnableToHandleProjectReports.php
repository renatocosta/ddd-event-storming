<?php

namespace Domain\Model\ProjectReports;

use Common\Exception\UnableToHandleException;

class UnableToHandleProjectReports extends UnableToHandleException
{

    const MISMATCH_EVENT_TYPE = 'The provided event_type %s is incorrect.';

    const STATUS_TRANSITION_VIOLATION = 'Status can not be changed from %s to %s';

    const UNKNOWN_PROJECT_STATE_ON_REPORTING = 'Unknown project state while reporting the status %s';

    const MISMATCH_PROJECT_STATUS = 'The provided status %s is incorrect or does not exist.';

    const MISMATCH_NEXT_PROJECT_STATUS = 'The next provided status %s is incorrect or does not exist.';

    const MISMATCH_INITAL_PROJECT_STATUS = 'The inital provided status %s is incorrect.';

    const MISMATCH_REPLICATION_STATUS = 'The provided status %s can not be replicated.';
}
