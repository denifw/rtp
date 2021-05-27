<?php

namespace App\Frame\System;

use App\Frame\Exceptions\Message;
use App\Frame\Formatter\Trans;
use App\Frame\System\Session\UserSession;
use App\Model\Dao\User\UserGroupDao;

class ApiAccess
{

    /**
     * Property to store all the right for current page.
     *
     * @var array
     */
    private $Access;

    /**
     * Base model constructor.
     *
     */
    public function __construct()
    {
        $this->Access = [];
    }

    /**
     * Function load the page.
     *
     * @param UserSession $user To store the user mapping id.
     *
     * @return void
     */
    public function loadAccess(UserSession $user): void
    {
        $this->Access = UserGroupDao::loadApiAccess($user->getSsId(), $user->getMappingId());
    }

    /**
     * Function get all access.
     *
     * @return array
     */
    public function getAllAccess(): array
    {
        return $this->Access;
    }

    /**
     * Function check is allow access job as customer
     *
     * @return bool
     */
    public function allowSeeJobAsRelation(): bool
    {
        return $this->checkApiAccess('AllowSeeJobAsRelation');
    }

    /**
     * Function check is allow access all job
     *
     * @return bool
     */
    public function allowSeeAllJob(): bool
    {
        return $this->checkApiAccess('AllowSeeAllJob');
    }

    /**
     * Function check is allow access all job
     *
     * @return bool
     */
    public function allowSeeAllOfficeJob(): bool
    {
        return $this->checkApiAccess('AllowSeeAllOfficeJob');
    }

    /**
     * Function check is allow update job action.
     *
     * @return bool
     */
    public function allowUpdateJobAction(): bool
    {
        return $this->checkApiAccess('AllowUpdateJobAction');
    }

    /**
     * Function check is allow update truck arrive data.
     *
     * @return bool
     */
    public function allowUpdateTruckArrive(): bool
    {
        return $this->checkApiAccess('AllowUpdateArrivalOfTruck');
    }

    /**
     * Function to generate the system settings.
     *
     * @param string $accessCode To store the access code.
     *
     * @return bool
     */
    private function checkApiAccess(string $accessCode): bool
    {
        return in_array($accessCode, $this->Access, true);
    }
}
