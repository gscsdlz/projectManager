<?php
/**
 * Created by PhpStorm.
 * User: 南宫悟
 * Date: 2018/2/12
 * Time: 8:47
 */

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;

class NormalUserCheck
{
    public function handle(Request $request, Closure $next)
    {
        if(!Session::has('user_id'))
            if (!$request->ajax()) {
                return response()->view('login');
            } else {
                return response()->json([
                    'status' => false,
                    'info' => 'login first',
                ]);
            }
        return $next($request);
    }
}