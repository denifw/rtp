<?php

namespace App\Frame\Mvc;

use App\Frame\Exceptions\Message;
use App\Frame\Gui\Html\Field;
use App\Frame\System\PageSetting;
use App\Frame\System\Session\UserSession;
use App\Frame\System\Validation;
use App\Frame\System\View;
use Illuminate\Http\UploadedFile;

abstract class AbstractBaseModel
{

    /**
     * Property to store the user data.
     *
     * @var UserSession $User
     */
    protected $User;

    /**
     * Property to store the base path of the model
     *
     * @var array $BasePath
     */
    protected static $BasePath = 'App/Model';

    /**
     * Property to store all the file name of the view.
     *
     * @var View $View
     */
    protected $View;

    /**
     * Property to store the right of the page.
     *
     * @var PageSetting $PageSetting
     */
    protected $PageSetting;

    /**
     * Property to store the validation handler.
     *
     * @var Validation $Validation
     */
    protected $Validation;

    /**
     * Attribute for the field object.
     *
     * @var Field $Field
     */
    protected $Field;

    /**
     * Property to store the name space of the model.
     *
     * @var string $NameSpace
     */
    protected $NameSpace = '';
    /**
     * Property to store the name space of the model.
     *
     * @var array $Parameters
     */
    private $Parameters = [];

    /**
     * Property to store popup trigger to hide some layout.
     *
     * @var array $PopupLayout
     */
    protected $PopupLayout = false;

    /**
     * Base model constructor.
     *
     */
    public function __construct()
    {
        $this->User = new UserSession();
    }

    /**
     * Function to get name space of the model.
     *
     * @return string
     */
    public function getNameSpaceModel(): string
    {
        return $this->NameSpace;
    }

    /**
     * Function to get log in user.
     *
     * @return UserSession
     */
    public function getUser(): UserSession
    {
        return $this->User;
    }

    /**
     * Function to set post value from the request.
     *
     * @param array $parameters To store the list input from request.
     *
     * @return void
     */
    public function setParameters(array $parameters): void
    {
        if (empty($parameters) === false) {
            $this->Parameters = array_merge($this->Parameters, $parameters);
        }
    }

    /**
     * Function to set parameter value by key.
     *
     * @param string $key   To store the key of the value
     * @param string $value To store the value
     *
     * @return void
     */
    public function setParameter($key, $value): void
    {
        if (empty($key) === false) {
            $this->Parameters[$key] = $value;
        }
    }


    /**
     * Function to get array parameter
     *
     * @param string $key To store the key of the value
     *
     * @return array
     */
    public function getArrayParameter($key): array
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
    public function getFloatParameter($key, $default = null): ?float
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
    public function getIntParameter($key, $default = null): ?int
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
    public function getFileParameter($key, $default = null): ?UploadedFile
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
    public function getStringParameter($key, $default = null): ?string
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
     * @param string $key     To store the key of the value
     * @param bool   $isShow  To trigger if the show true then get the parameter else return empty string.
     * @param string $default To trigger if the show true then get the parameter else return empty string.
     *
     * @return string
     */
    public function getParameterForModal($key, bool $isShow = false, $default = ''): string
    {
        $result = $default;
        if ($isShow === true) {
            $result = $this->getStringParameter($key, $default);
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
    public function isValidParameter($key): bool
    {
        $result = false;
        if (array_key_exists($key, $this->Parameters) === true && empty($this->Parameters[$key]) === false) {
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
    public function isExistParameter($key): bool
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
    public function getAllParameters(): array
    {
        return $this->Parameters;
    }

    /**
     * Method to get the namespace from the url.
     *
     * @param string $pageCategory To store the page category of the model.
     * @param string $nameSpace    To store the name space of the page.
     *
     * @return void
     */
    protected function loadNameSpaceModel(string $pageCategory, string $nameSpace): void
    {
        $nameSpace = str_replace('\\', '/', $nameSpace);
        $basePathPage = self::$BasePath . '/' . $pageCategory . '/';
        $this->NameSpace = substr($nameSpace, strlen($basePathPage), strlen($nameSpace));
    }

}
