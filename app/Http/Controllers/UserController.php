<?php
/**
 * Created by PhpStorm.
 * User: 南宫悟
 * Date: 2018/2/10
 * Time: 16:42
 */

namespace App\Http\Controllers;


use App\Model\UserModel;
use App\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;

class UserController
{
    public function index(Request $request)
    {
        return view('user', [
            'menu' => 'userManager'
        ]);
    }

    public function show(Request $request)
    {
        LogController::insertLog("请求显示用户", $request);

        $res = UserModel::get();

        $data = [];
        foreach ($res as $row) {
            $data[] = [
                $row->user_id,
                $row->username,
                '******',
                $row->privilege,
                $row->created_at->toDateString(),
                date('Y-m-d H:i:s', $row->last_time),
                $row->last_ip,
            ];
        }

        return response()->json([
            'status' => true,
            'data' => $data,
        ]);
    }

    public function dels(Request $request)
    {
        LogController::insertLog("删除用户", $request);

        $ids = $request->get('ids');

        $row = UserModel::destroy($ids);
        if($row == 0)
            return response()->json(['status' => false]);
        else
            return response()->json([
                'status' => true,
                'ids' => $ids,
            ]);
    }

    public function save(Request $request)
    {
        LogController::insertLog("保存用户信息", $request);


        $infos = $request->get('infos');
        foreach ($infos as $pro) {
            UserModel::where('user_id', $pro[0])->update([
                'username' => $pro[1],
                'privilege' => $pro[3],
            ]);
        }

        return response()->json([
            'status' => true,
        ]);
    }

    public function add(Request $request)
    {
        LogController::insertLog("添加用户", $request);

        $info = $request->get('info');

        $errors = [];
        if(!isset($info[0]) || strlen($info[0]) == 0)
            $errors[] = ['0', '用户名不能为空'];

        if(!isset($info[1]))
            $errors[] = ['1', '未选择权限'];

        $c = UserModel::where('username', $info[0])->count();
        if($c != 0)
            $errors[] = ['0', '用户名重复'];
        if(empty($errors)) {
            $user = new UserModel();
            $user->username = $info[0];
            $user->privilege = $info[1];
            $user->password = sha1("123456");
            $user->save();
            return response()->json([
                'status' => true
            ]);
        } else {
            return response()->json([
                'status' => false,
                'errors' => $errors,
            ]);
        }


    }

    public function changePass(Request $request)
    {
        LogController::insertLog("用户修改密码", $request);

        $pass1 = $request->get('pass1');
        $pass2 = $request->get('pass2');
        $user_id = $request->get('user_id');

        if($pass1 == $pass2 && strlen($pass1) > 0) {
            $res = UserModel::where('user_id', $user_id)->update(
                ['password' => sha1($pass1)]
            );
            return response()->json([
                'status' => $res == 0 ? false : true,
            ]);
        } else {
            return response()->json(
                [
                    'status' => false,
                    'info' => '密码不匹配',
                ]
            );
        }
    }

    public function login(Request $request)
    {
        if($request->getMethod() == "POST") {
            $username = $request->get('username');
            $password = $request->get('password');

            $user = UserModel::select('username', 'password', 'privilege', 'user_id')
                ->where('username', $username)->first();
            if (isset($user) && $user->username == $username && $user->password == sha1($password)) {
                Session::put('user_id', $user->user_id);
                Session::put('username', $user->username);
                Session::put('pri', $user->privilege);
                UserModel::where('user_id', $user->user_id)->update([
                    'last_time' => time(),
                    'last_ip' => $request->server('REMOTE_ADDR'),
                ]);

                LogController::insertLog("登录", $request);


                return response()->json([
                    'status' => true,
                ]);
            } else {
                return response()->json([
                    'status' => false,
                ]);
            }
        } else {
            if(Session::has('user_id'))
                return response()->redirectTo('/');
            else
                return view('login');
        }
    }

    public function logout(Request $request)
    {
        LogController::insertLog("退出登录", $request);

        Session::flush();
        return response()->redirectTo('login')
            ->cookie ( 'laravel_session', '', time () - 3600, '/', '', 0, 0 );;
    }
}