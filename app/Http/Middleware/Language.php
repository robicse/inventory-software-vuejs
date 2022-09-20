<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\App;
use DB, Session, Config;

class Language
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {


        if (Session::has('language_setting')) {
            $locale = Session::get('language_setting', Config::get('app.locale'));
        } else {

            $locale = 'english';
        }

        App::setLocale($locale);

        return $next($request);

    }
}
