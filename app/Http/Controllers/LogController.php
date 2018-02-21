<?php
/**
 * Created by PhpStorm.
 * User: 南宫悟
 * Date: 2018/2/10
 * Time: 16:45
 */

namespace App\Http\Controllers;


use App\Model\LogModel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;

class LogController
{
    public function index(Request $request)
    {
        return view('log', [
            'menu' => 'logManager'
        ]);
    }

    public function show(Request $request)
    {
        $page = $request->get('currentPage', 1);
        $total = LogModel::count();
        $pms = 20;

        if($page <= 0)
            $page = 1;
        if($page > (int)(($total - 1) / $pms) + 1)
            $page = (int)(($total - 1) / $pms) + 1;

        $res = LogModel::where([
            ['log_id', '>', ($page - 1) * $pms],
            ['log_id', '<=', $page * $pms]
        ])->get();
        $data = [];
        foreach ($res as $row) {
            $tmp = json_decode($row->args, true);
            array_unshift($tmp, $row->log_id);
            $data[] = $tmp;
        }
        return response()->json(
            [
                'status' => true,
                'currentPage' => $page,
                'totalPage' => (int)(($total - 1) / $pms) + 1,
                'data' => $data,
            ]
        );
    }

    public static function insertLog($function_name, Request $request)
    {
       $ua = $request->server('HTTP_USER_AGENT');
       $method = $request->server('REQUEST_METHOD');
       $ip = $request->server('REMOTE_ADDR');
       $json = json_encode(
         [Session::get('username'), $function_name, $method, date('Y-m-d H:i:s'), $ip, $ua]
       );
       DB::table('log')->insert([
            'args' => $json,
       ]);
    }
}