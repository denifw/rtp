<?php

namespace App\Http\Middleware;

use App\Frame\Formatter\Trans;
use App\Frame\System\Session\UserSession;
use App\Model\Dao\System\Access\UserTokenDao;
use Closure;
use Illuminate\Support\Facades\App;

class Authorize
{
    /**
     * Handle an incoming request.
     *
     * @param \Illuminate\Http\Request $request
     * @param \Closure $next
     *
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        $user = new UserSession();
        if ($user->isSet() === false) {
            return redirect('/login');
        }
        if (empty($user->getLanguageIso()) === false) {
            $lg = $user->getLanguageIso();
        } else {
            $lg = $user->Settings->getLanguageIso();
        }
        if (empty($lg) === true) {
            $lg = 'id';
        }
        App::setLocale($lg);

        # IF the user exist then manage the access.
        $userTokenDao = new UserTokenDao();
        $access = $userTokenDao->manageUserToken($user->getAllData());
        $message = '';
        $messageCode = 0;
        if ($access !== 'VALID') {
            if ($access === 'DESTROY') {
                $message = Trans::getWord('destroy_access', 'message');
                $messageCode = 1;
            } elseif ($access === 'EXPIRED') {
                $message = Trans::getWord('expired_access', 'message');
                $messageCode = 2;
            }
            if ($request->ajax() === true) {
                return response()->json([
                    'success' => false,
                    'errors' => $message
                ]);
            }

            return redirect('/login?m=' . $messageCode);
        }

        return $next($request);
    }
}
