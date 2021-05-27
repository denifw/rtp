<?php

/**
 * Contains code written by the C-Book Software.
 * Any other use of this code is in violation of copy rights.
 *
 * @package   Matalogix
 * @author    Deni Firdaus Waruwu <deni.fw@spada-informatika.com>
 * @copyright 2019 Matalogix
 */

namespace App\Model\Api\Alfa;

use App\Frame\Mvc\AbstractBaseApi;
use App\Frame\System\Session\UserSession;
use App\Model\Dao\User\UserMappingDao;
use App\Model\Dao\User\UserMobileTokenDao;
use App\Model\Dao\User\UsersDao;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;

/**
 * Class to handle data access object for table user_group.
 *
 * @package    app
 * @subpackage Model\Api
 * @author     Deni Firdaus Waruwu <deni.fw@spada-informatika.com>
 * @copyright  2019 Matalogix
 */
class Auth extends AbstractBaseApi
{

    /**
     * Function to load the validation role.
     *
     * @return void
     */
    protected function loadValidationRole(): void
    {
        if ($this->ActionName === 'loginByUsernamePassword') {
            $this->Validation->checkRequire('us_username');
            $this->Validation->checkRequire('us_password');
        }
    }

    /**
     * Function to generate the system settings.
     *
     *
     * @return JsonResponse
     */
    public function loadAuthResponse(): JsonResponse
    {

        $this->loadValidationRole();

        if ($this->isValidInputs() === false) {
            return $this->returnErrorResponse('400');
        }
        try {
            $this->doControl();
        } catch (\Exception $e) {
            $this->setErrorCode('500');
        }

        if (empty($this->ErrorCode) === false) {
            return $this->returnErrorResponse($this->ErrorCode);
        }

        return $this->returnSuccessResponse();
    }


    /**
     * Abstract function to update data in database.
     *
     * @return void
     */
    protected function doControl(): void
    {
        if ($this->ActionName === 'loginByUsernamePassword') {
            $this->doLoginByUsernameAndPassword();
            if ($this->User->isSet()) {
                $this->doUpdateUserMappingToken();
                $this->addResultData('user', $this->User->getAllData());
                $this->Access->loadAccess($this->User);
                $this->addResultData('access', $this->Access->getAllAccess());
            } else {
                $this->addResultData('user', []);
                $this->addResultData('access', []);
            }
        } else if ($this->ActionName === 'loginByToken') {
            $this->addResultData('user', $this->User->getAllData());
            $this->addResultData('access', $this->Access->getAllAccess());
        } else if ($this->ActionName === 'loadUserMapping') {
            if ($this->User->isUserSystem() === false) {
                $data = UserMappingDao::loadAllUserMappingData($this->User->getId(), $this->User->getSsId());
            } else {
                $data = UserMappingDao::loadAllUserMappingDataForSystem($this->User->getSsId());
            }
            $this->addResultData('systems', $data);
        }
    }

    /**
     * Abstract function to update data in database.
     *
     * @return void
     */
    protected function doUpdateUserMappingToken(): void
    {
        DB::beginTransaction();
        try {
            $umt = new UserMobileTokenDao();
            $token = md5(time() . $this->User->getId()) . $this->User->getId();
            $umt->doDeleteTransactionByUsId($this->User->getId());
            $colVal = [
                'umt_us_id' => $this->User->getId(),
                'umt_api_token' => $token,
                'umt_created_on' => date('Y-m-d H:i:s'),
            ];
            $umt->doInsertTransaction($colVal);
            $this->User->setApiToken($token);
            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            $this->setErrorCode('500');
        }
    }

    /**
     * Function to do login by username and password
     *
     * @return void
     */
    private function doLoginByUsernameAndPassword(): void
    {
        $userDao = new UsersDao();
        $user = $userDao->getLoginData($this->getStringParameter('us_username'), $this->getStringParameter('us_password'));
        $this->User = new UserSession($user);
        if ($this->User->isSet() === false) {
            $this->setErrorCode('100');
        } else {
            if ($this->User->isUserSystem() === false) {
                $userSetting = UserMappingDao::loadUserMappingData($this->User->getId());
            } else {
                $userSetting = UserMappingDao::loadSystemMappingData();
            }
            if (empty($userSetting) === true) {
                $this->setErrorCode('101');
            } else {
                $this->User->setData(array_merge($this->User->getAllData(), $userSetting));
            }
        }
    }
}
