<?php

namespace App\Providers;

use App\Frame\Formatter\StringFormatter;
use App\Frame\Formatter\Trans;
use App\Frame\System\Session\UserSession;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\View;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        View::composer('*', function ($view) {
            $title = config('app.name');
            $copyRight = 'mbteknologi.com';
            $bodyClass = 'nav-md';
            $switcher = '';
            $logo = asset('images/image-not-found.jpg');
            $user = new UserSession();
            if ($user->isSet()) {
                $view->with('user', $user->getAllData());
                if (empty($user->getMenuStyle()) === false) {
                    $bodyClass = $user->getMenuStyle();
                }
                $ns = StringFormatter::replaceSpecialCharacter($user->Settings->getNameSpace(), '');
                $logoPath = 'storage/' . $ns . '/systemsetting/logo/' . $user->Settings->getLogo();
                if (file_exists(public_path($logoPath)) === true) {
                    $logo = asset($logoPath);
                }
                if ($user->isMappingEnabled()) {
                    $switcher = '<li><a href="' . url('switchSystem') . '"> ' . Trans::getWord('switchSystem') . '</a></li>';
                }
            }
            $view->with('body_class', $bodyClass);
            $view->with('app_name', $title);
            $view->with('app_logo', $logo);
            $view->with('switcher', $switcher);
            $view->with('copyright', $copyRight);
        });
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        //
    }
}
