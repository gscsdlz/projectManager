<?php
/**
 * Created by PhpStorm.
 * User: 南宫悟
 * Date: 2018/2/12
 * Time: 8:47
 */

namespace App\Http\Middleware;

use Closure;

class AdminUserCheck
{
    public function handle($request, Closure $next)
    {
        if(!Session::has('user_id'))
            return view('login');
        else if(Session::get('privilege') != 1)
            return response()->view('404', [],404);
        return $next($request);
    }
}