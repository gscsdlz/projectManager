<?php
/**
 * Created by PhpStorm.
 * User: 南宫悟
 * Date: 2018/2/8
 * Time: 8:56
 */

namespace App\Http\Controllers;


use App\Model\ProjectModel;
use Illuminate\Http\Request;

class ProjectController extends Controller
{
    public function index(Request $request)
    {
        return view('projectManager', [
            'menu' => 'projectManager',
        ]);
    }

    public function get(Request $request)
    {
        $pms = config('web.proManagerPageMax');
        $currentPage = $request->get('currentPage');

        $total = ProjectModel::count();
        $res = ProjectModel::select('project_id', 'project_name', 'project_attr', 'project_stime', 'project_etime', 'project_total1', 'project_total2', 'project_total3')
            ->offset(($currentPage - 1) * $pms)->limit($pms)->get();
        $data = [];
        foreach ($res as $row) {
            $tmp = [];
            $tmp[] = $row->project_id;
            $tmp[] = $row->project_name;
            $jtmp = json_decode($row->project_attr, true);
            $tmp[] = $jtmp[0];
            $tmp[] = $jtmp[1];
            $tmp[] = $jtmp[2];
            $tmp[] = $jtmp[3];

            $tmp[] = $row->project_total1;
            $tmp[] = $row->project_total2;
            $tmp[] = $row->project_total3;
            $tmp[] = date('Y-m-d', $row->project_stime);
            $tmp[] = date('Y-m-d', $row->project_etime);
            $data[] = $tmp;
        }


        return response()->json([
            'status' => true,
            'data' => $data,
            'currentPage' => $currentPage,
            'totalPage' => ($total - 1 ) / $pms + 1,
        ]);
    }

    public function dels(Request $request)
    {
        $ids = $request->get('ids');

        $row = ProjectModel::destroy($ids);
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
        $infos = $request->get('infos');
        foreach ($infos as $pro) {
            $pro_attr = json_encode([$pro[2], $pro[3], $pro[4], $pro[5]]);
            ProjectModel::where('project_id', $pro[0])->update([
                'project_name' => $pro[1],
                'short_name' => getFirstChars($pro[1]),
                'project_attr' => $pro_attr,
                'project_total1' => $pro[6],
                'project_total2' => $pro[7],
                'project_total3' => $pro[8],
                'project_stime' => strtotime($pro[9]),
                'project_etime' => strtotime($pro[10]),
            ]);
        }

        return response()->json([
            'status' => true,
        ]);
    }

    public function add(Request $request)
    {
        $info = $request->get('info');

        $errors = [];
        if(!isset($info[0]) || strlen($info[0]) == 0)
            $errors[] = ['0', '项目名称不能为空'];
        else if(ProjectModel::where('project_name', $info[0])->count() != 0)
                $errors = ['0', '项目名称重复'];
        else if(strlen($info[0]) >= 100)
            $errors = ['0', '字数超过限制'];

        if(!isset($info[8]))
            $errors[] = ['8', '项目开始时间为必填项'];
        else
            $stime = strtotime($info[8]);
        if(!isset($info[9]))
            $errors[] = ['9', '项目结束时间为必填项'];
        else
            $etime = strtotime($info[9]);

        if(isset($stime) && isset($etime) && $etime < $stime)
            $errors[] = ['9', '结束时间不能小于开始时间'];

        if(empty($errors)) {
            $pro = new ProjectModel();
            $pro->project_name = $info[0];
            $pro->project_stime = $stime;
            $pro->project_etime = $etime;
            $pro->project_total1 = $info[5];
            $pro->project_total2 = $info[6];
            $pro->project_total3 = $info[7];
            $pro->short_name = getFirstChars($info[0]);
            $pro->project_attr = json_encode([$info[1], $info[2], $info[3], $info[4]]);
            $pro->save();
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

    public function search(Request $request)
    {
        if($request->getMethod() == 'GET') {
            return view('projectManager', [
                'menu' => 'projectManager',
               'name' => $request->get('name')
            ]);
        }
        $name = $request->get('name');
        $data = [];
        if(strlen($name) == mb_strlen($name)) {
            $key = strtoupper($name);
            $res = ProjectModel::select('project_id', 'project_name', 'project_attr', 'project_stime', 'project_etime', 'project_total1', 'project_total2', 'project_total3')
                ->where('short_name', 'like', '%' . $key . '%')->get();
        } else {
            $res = ProjectModel::select('project_id', 'project_name', 'project_attr', 'project_stime', 'project_etime', 'project_total1', 'project_total2', 'project_total3')
                ->where('project_name', 'like', '%' . $name . '%')->get();
        }
        foreach ($res as $row) {
            $tmp = [];
            $tmp[] = $row->project_id;
            $tmp[] = $row->project_name;
            $jtmp = json_decode($row->project_attr, true);
            $tmp[] = $jtmp[0];
            $tmp[] = $jtmp[1];
            $tmp[] = $jtmp[2];
            $tmp[] = $jtmp[3];

            $tmp[] = $row->project_total1;
            $tmp[] = $row->project_total2;
            $tmp[] = $row->project_total3;
            $tmp[] = date('Y-m-d', $row->project_stime);
            $tmp[] = date('Y-m-d', $row->project_etime);
            $data[] = $tmp;
        }
        return response()->json([
            'status' => true,
            'data' => $data,
        ]);
    }

    public function getList(Request $request)
    {
        $page = $request->get('page', 1);
        $pms = config('web.proManagerPageMax');
        $total = ProjectModel::count();
        if($page > (int)(($total - 1) / $pms) + 1)
            $page = (int)(($total - 1) / $pms) + 1;
        $res = ProjectModel::select('project_id', 'project_name')
            ->offset(($page - 1) * $pms)->limit($pms)->get();
        return response()->json([
            'status' => true,
            'page' => $page,
            'data' => $res,
        ]);
    }

    public function getAllList(Request $request)
    {
        $res = ProjectModel::select('project_id', 'project_name', 'short_name')
           ->get();
        $data = [];
        foreach($res as $row) {
            $data[] = [
                $row->project_name,
                $row->project_id,
                $row->short_name,
            ];
        }
        return response()->json([
            'status' => true,
            'data' => $data,
        ]);
    }
}