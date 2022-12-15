<?php

namespace App\Http\Middleware;

use App\GeneralSettings;
use Closure;
use Illuminate\Support\Facades\App;

class DefineConstants
{
    /**
     * Handle an incoming request.
     *
     * @param \Illuminate\Http\Request $request
     * @param \Closure $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        $language = ($request->header('Accept-Language')) ? $request->header('Accept-Language') : 'en';
         App::setLocale($language);
         
        GeneralSettings::define_const();
        return $next($request);
    }
}
