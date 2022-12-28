<?php

namespace Domain\Model\ProjectReports\State;

use Domain\Model\ProjectReports\ProjectReportsStatus;
use Domain\Model\ProjectReports\UnableToHandleProjectReports;
use Exception;

interface ProjectReportsStateable
{

    /**
     * @throws UnableToHandleProjectReports
     * @throws Exception
     * @return ProjectReportsStatus
     */
    public function confirm(): ProjectReportsStatus;

    /**
     * @throws UnableToHandleProjectReports
     * @throws Exception
     * @return ProjectReportsStatus
     */
    public function markCreditCardAuthAsFailed(): ProjectReportsStatus;

    /**
     * @throws UnableToHandleProjectReports
     * @throws Exception
     * @return ProjectReportsStatus
     */
    public function markCreditCardAuthAsSucceded(): ProjectReportsStatus;

    /**
     * @throws UnableToHandleProjectReports
     * @throws Exception
     * @return ProjectReportsStatus
     */
    public function acceptOrder(): ProjectReportsStatus;

    /**
     * @throws UnableToHandleProjectReports
     * @throws Exception
     * @return ProjectReportsStatus
     */
    public function startProject(): ProjectReportsStatus;

    /**
     * @throws UnableToHandleProjectReports
     * @throws Exception
     * @return ProjectReportsStatus
     */
    public function finishProject(): ProjectReportsStatus;

    /**
     * @throws UnableToHandleProjectReports
     * @throws Exception
     * @return ProjectReportsStatus
     */
    public function reportProject(): ProjectReportsStatus;

    /**
     * @throws UnableToHandleProjectReports
     * @throws Exception
     * @return ProjectReportsStatus
     */
    public function cancelProject(): ProjectReportsStatus;

    /**
     * @throws UnableToHandleProjectReports
     * @throws Exception
     * @return ProjectReportsStatus
     */
    public function finishPayment(): ProjectReportsStatus;

    /**
     * @throws UnableToHandleProjectReports
     * @throws Exception
     * @return ProjectReportsStatus
     */
    public function markPaymentAsFailed(): ProjectReportsStatus;
}
