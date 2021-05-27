<?php

namespace App\Http\Controllers;

use App\Frame\System\Session\UserSession;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class Controller extends BaseController
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;


    /**
     * Function to get custom path.
     *
     * @param string $path To store the path of the model.
     *
     * @return string
     */
    protected function getCustomPath(string $path): string
    {
        $user = new UserSession();
        if (empty($path) === true || $user->isSet() === false) {
            return '';
        }
        return '\\App\\Custom\\' . $user->Settings->getNameSpace() . '\\' . $path;
    }


    /**
     * Method to validate if view class exists by using the autoload function from php.
     *
     * @param string $namespace The full namespace string to validate the model class to see if it exists.
     *
     * @return boolean
     */
    protected function validateModelClass($namespace): bool
    {
        return class_exists($namespace);
    }

    /**
     * Function to check is form token valid or not
     *
     * @return bool
     */
    protected function isTokenFormValid(): bool
    {
        return !empty(request('_token'));
    }

    /**
     * Function to get the form action parameter
     *
     * @return string
     */
    protected function getFormAction(): string
    {

        return 'main_form_action';
    }


}
