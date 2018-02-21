<?php
/**
 * Created by PhpStorm.
 * User: 南宫悟
 * Date: 2018/2/12
 * Time: 8:47
 */

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Session;

class NormalUserCheck
{
    public function handle($request, Closure $next)
    {
        if(!Session::has('user_id'))
            return response()->view('login');
        return $next($request);
    }
}