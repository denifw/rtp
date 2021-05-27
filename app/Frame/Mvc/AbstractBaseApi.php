<?php

namespace App\Frame\Mvc;

use App\Frame\Exceptions\WarningException;
use App\Frame\Formatter\DataParser;
use App\Frame\Formatter\Trans;
use App\Frame\System\ApiAccess;
use App\Frame\System\Session\UserSession;
use App\Frame\System\Validation;
use App\Model\Dao\User\UserMappingDao;
use App\Model\Dao\User\UsersDao;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\UploadedFile;

abstract class AbstractBaseApi
{
    /**
     * Property to store the user data.
     *
     * @var UserSession $User
     */
    protected $User;

    /**
     * Property to store the user data.
     *
     * @var Validation $Validation
     */
    protected $Access;

    /**
     * Property to store the validation handler.
     *
     * @var Validation $Validation
     */
    protected $Validation;

    /**
     * Property to store the name space of the model.
     *
     * @var array $Parameters
     */
    private $Parameters = [];

    /**
     * Property to store the results data.
     *
     * @var array $Results
     */
    private $Results = [];

    /**
     * Property to store the error message.
     *
     * @var string $ErrorCode
     */
    protected $ErrorCode = '';

    /**
     * Property to store the error message.
     *
     * @var string $ErrorMessage
     */
    protected $ErrorMessage = '';

    /**
     * Property to store the action name.
     *
     * @var string $ActionName
     */
    protected $ActionName = '';

    /**
     * Base model constructor.
     *
     * @param string $actionName To store the action name.
     * @param array  $parameters To store all the parameters.
     */
    public function __construct(string $actionName, array $parameters = [])
    {
        $this->ActionName = $actionName;
        $this->setParameters($parameters);
        $this->Access = new ApiAccess();
        $this->Validation = new Validation();
    }

    /**
     * Abstract function to control the model
     *
     * @return void
     */
    abstract protected function doControl(): void;


    /**
     * Function to load the validation role.
     *
     * @return void
     */
    protected function loadValidationRole(): void
    {
    }

    /**
     * Function to set post value from the request.
     *
     * @param array $parameters To store the list input from request.
     *
     * @return void
     */
    protected function setParameters(array $parameters): void
    {
        if (empty($parameters) === false) {
            $this->Parameters = array_merge($this->Parameters, $parameters);
        }
    }

    /**
     * Function to set the error code
     *
     * @param string $errorCode To store the error code
     *
     * @return void
     */
    protected function setErrorCode(string $errorCode): void
    {
        $this->ErrorCode = $errorCode;
    }

    /**
     * Function to set the error code
     *
     * @param string $message To store the error code
     *
     * @return void
     */
    protected function setErrorMessage(string $message): void
    {
        $this->ErrorMessage = $message;
    }

    /**
     * Function to set parameter value by key.
     *
     * @param string $key   To store the key of the value
     * @param string $value To store the value
     *
     * @return void
     */
    protected function setParameter($key, $value): void
    {
        if (empty($key) === false) {
            $this->Parameters[$key] = $value;
        }
    }


    /**
     * Function to generate the system settings.
     *
     * @return JsonResponse
     */
    protected function returnSuccessResponse(): JsonResponse
    {
        return response()->json([
            'success' => true,
            'response_code' => '200',
            'message' => '',
            'results' => $this->Results,
        ]);
    }


    /**
     * Function to generate the system settings.
     *
     * @param string $responseCode To store the error message.
     * @param string $message      To store the error message.
     *
     * @return JsonResponse
     */
    protected function returnErrorResponse(string $responseCode, $message = ''): JsonResponse
    {
        if (empty($message) === true) {
            $message = $this->ErrorMessage;
        }
        if (empty($message) === true) {
            $message = Trans::getWord($responseCode, 'api_response');
        }
        return response()->json([
            'success' => false,
            'response_code' => $responseCode,
            'message' => $message,
            'results' => [],
        ]);
    }

    /**
     * Function to generate the system settings.
     *
     * @return JsonResponse
     */
    public function loadResponse(): JsonResponse
    {
        try {
            if ($this->isValidParameter('us_api_token') === true && $this->isValidParameter('ss_id') === true) {
                $this->loadUserByToken();
            }

            if ($this->User->isSet()) {
                # Load User Access
                $this->Access->loadAccess($this->User);

                # Load Validation
                $this->loadValidationRole();
                if ($this->isValidInputs() === false) {
                    return $this->returnErrorResponse('400', $this->Validation->getFirstErrorMessage());
                }
                # Do Control
                $this->doControl();
            } else {
                $this->setErrorCode('100');
            }
        } catch (\Exception $e) {
            if($e instanceof WarningException) {
                $this->setErrorCode('400');
                $this->setErrorMessage($e->getMessage());
            } else {
                $this->setErrorCode('500');
//                if ($this->getIntParameter('debug', 0) === 1) {
                    $this->setErrorMessage($e->getMessage());
//                }
            }
        }

        if (empty($this->ErrorCode) === false) {
            return $this->returnErrorResponse($this->ErrorCode);
        }

        return $this->returnSuccessResponse();
    }

    /**
     * Function to get array parameter
     *
     * @param string $key To store the key of the value
     *
     * @return array
     */
    protected function getArrayParameter($key): array
    {
        $result = [];
        if (array_key_exists($key, $this->Parameters) === true && is_array($this->Parameters[$key]) === true) {
            $result = $this->Parameters[$key];
        }

        return $result;
    }

    /**
     * Function to get float parameter value.
     *
     * @param string $key     To store the key of the value
     * @param float  $default To store the default value if the parameter is empty
     *
     * @return null|float
     */
    protected function getFloatParameter($key, $default = null): ?float
    {
        $result = $default;
        if (array_key_exists($key, $this->Parameters) === true && is_numeric($this->Parameters[$key]) === true) {
            $result = (float)$this->Parameters[$key];
        }

        return $result;
    }

    /**
     * Function to get parameter value.
     *
     * @param string  $key     To store the key of the value
     * @param integer $default To store the default value if the parameter is empty
     *
     * @return null|integer
     */
    protected function getIntParameter($key, $default = null): ?int
    {

        $result = $default;
        if (array_key_exists($key, $this->Parameters) === true && is_numeric($this->Parameters[$key]) === true) {
            $result = (int)$this->Parameters[$key];
        }

        return $result;
    }

    /**
     * Function to get parameter value.
     *
     * @param string  $key     To store the key of the value
     * @param integer $default To store the default value if the parameter is empty
     *
     * @return null|UploadedFile
     */
    protected function getFileParameter($key, $default = null): ?UploadedFile
    {

        $result = $default;
        if (array_key_exists($key, $this->Parameters) === true) {
            $result = $this->Parameters[$key];
        }

        return $result;
    }

    /**
     * Function to get string parameter value.
     *
     * @param string $key     To store the key of the value
     * @param string $default To store the default value if the parameter is empty
     *
     * @return string
     */
    protected function getStringParameter($key, $default = null): ?string
    {
        $result = $default;
        if (array_key_exists($key, $this->Parameters) === true && empty($this->Parameters[$key]) === false) {
            $result = $this->Parameters[$key];
        }

        return $result;
    }

    /**
     * Function to get string parameter value.
     *
     * @param string $key    To store the key of the value
     * @param bool   $isShow To trigger if the show true then get the parameter else return empty string.
     *
     * @return string
     */
    protected function getParameterForModal($key, bool $isShow = false): string
    {
        $result = '';
        if ($isShow === true) {
            $result = $this->getStringParameter($key, '');
        }

        return $result;
    }

    /**
     * Function to check is the parameter has value or not.
     *
     * @param string $key To store the key of the value
     *
     * @return bool
     */
    protected function isValidParameter($key): bool
    {
        $result = false;
        if (array_key_exists($key, $this->Parameters) === true && $this->Parameters[$key] !== null && trim($this->Parameters[$key]) !== '') {
            $result = true;
        }

        return $result;
    }

    /**
     * Function to check is the parameter has value or not.
     *
     * @param string $key To store the key of the value
     *
     * @return bool
     */
    protected function isExistParameter($key): bool
    {
        $result = false;
        if (array_key_exists($key, $this->Parameters) === true) {
            $result = true;
        }

        return $result;
    }

    /**
     * Function to get all parameter.
     *
     * @return array
     */
    protected function getAllParameters(): array
    {
        return $this->Parameters;
    }


    /**
     * Function to add data into the results property
     *
     * @param string $resultId To store the response id.
     * @param mixed  $data     To store the response data.
     *
     * @return void
     */
    protected function addResultData(string $resultId, $data): void
    {
        if (empty($resultId) === false) {
            $this->Results[$resultId] = $data;
        }
    }

    /**
     * Function to add remove data.
     *
     * @param string $resultId To store the response id.
     *
     * @return void
     */
    protected function removeResultData(string $resultId): void
    {
        if (empty($resultId) === false && array_key_exists($resultId, $this->Results) === true) {
            $temp = [];
            foreach ($this->Results as $key => $val) {
                if ($key !== $resultId) {
                    $temp[$key] = $val;
                }
            }
            $this->Results = $temp;
        }
    }


    /**
     * Function to get name space of the model.
     *
     * @return boolean
     */
    protected function isValidInputs(): bool
    {
        if ($this->Validation->isValidated() === false) {
            $this->Validation->setInputs($this->Parameters);
            $this->Validation->doValidation();
        }

        return $this->Validation->isValidInputs();
    }


    /**
     * Function to do login by username and password
     *
     * @return void
     */
    private function loadUserByToken(): void
    {
        $userDao = new UsersDao();
        $ssId = $this->getIntParameter('ss_id');
        $user = $userDao->getLoginDataByToken($this->getStringParameter('us_api_token'));
        if (empty($user) === true) {
            $this->setErrorCode('102');
        } else {
            if (empty($user['umt_deleted_on']) === false) {
                $this->setErrorCode('103');
            } else {
                if ($user['us_system'] === 'N') {
                    $userSetting = UserMappingDao::loadUserMappingData($user['us_id'], $ssId);
                } else {
                    $userSetting = UserMappingDao::loadSystemMappingData($ssId);
                }
                if (empty($userSetting) === true) {
                    $this->setErrorCode('101');
                } else {
                    $user = array_merge($user, $userSetting);
                }
            }
        }
        $this->User = new UserSession($user);
    }

    /**
     * Function to load list event for action
     *
     * @param array  $data  To store sql result data
     * @param string $value To store the column id for the value.
     * @param string $text  To store the column id for the text.
     *
     * @return array
     */
    protected function doPrepareSingleSelectData(array $data, string $value, string $text): array
    {
        $result = [];
        if (empty($data) === false) {
            foreach ($data as $row) {
                $result[] = DataParser::doFormatApiData([
                    'text' => $row[$text],
                    'value' => $row[$value],
                ]);
            }
        }

        return $result;
    }
}
