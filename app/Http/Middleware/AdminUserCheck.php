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

class AdminUserCheck
{
    public function handle(Request $request, Closure $next)
    {
        if (Session::get('privilege') != 1)
            if (!$request->ajax()) {
                return response()->view('errors.404', [], 404);
            } else {
                return response()->json([
                    'status' => false,
                    'info' => 'privilege error',
                ]);
            }
        return $next($request);
    }
}