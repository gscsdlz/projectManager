<?php
/**
 * Created by PhpStorm.
 * User: 南宫悟
 * Date: 2018/1/2
 * Time: 14:59
 */

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class IndexController extends Controller
{

    public function import(Request $request)
    {
        return view('import', [
            'menu' => 'import'
        ]);
    }

    public function index(Request $request)
    {
        return view('index', [
            'menu' => 'index'
        ]);
    }

    public function test(Request $request)
    {
        dd(strtotime('2018-02-14'));
    }
}