<?php
/**
 * Created by PhpStorm.
 * User: 南宫悟
 * Date: 2018/2/9
 * Time: 10:08
 */

namespace App\Http\Controllers;


use App\Model\ProjectModel;
use App\Model\RecordModel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class RecordController extends Controller
{
    public function index(Request $request, $project_id = 0)
    {
        $project = ProjectModel::select("project_id", "project_name")->where('project_id', $project_id)->first();
        if(is_null($project))
            return view('insert', [
                'menu' => 'insert'
            ]);
        else
            return view('insert', [
               'project_name' => $project->project_name,
               'project_id' => $project->project_id,
                'menu' => 'insert'
            ]);
    }

    public function insert(Request $request)
    {
        $project_id = $request->get('project_id');
        $data = $request->get('data');
        $date = $request->get('date');
        $args = [];
        foreach ($data as $row) {
            $args[] = [
                'project_id' => $project_id,
                'member_id' => $row[0],
                'project_total1' => (float)$row[1],
                'project_total2' => (float)$row[2],
                'content' => $row[3],
                'created_at' => date('Y-m-d H:i:s', time()),
                'record_time' => strtotime($date),
            ];
        }
        $res = DB::table('record')->insert($args);
        return response()->json([
            'status' => true,
            'ids' => $res
        ]);

    }

    public function search(Request $request)
    {
        $mid = $request->get('member_id', 0);
        $pid = $request->get('project_id', 0);
        $stime = $request->get('stime', strtotime(date('Y-m-d')));
        $etime = $request->get('etime', strtotime(date('Y-m-d')));
        $page = $request->get('page', 1);

        if($etime < $stime) {
            return response()->json([
                'status' => false,
                'info' => '时间错误',
            ]);
        }
        $res = RecordModel::select('record_id', 'content', 'record_time', 'record.project_total1', 'record.project_total2', 'project_name', 'member_name')
            ->leftJoin('project', 'project.project_id' , '=', 'record.project_id')
            ->leftJoin('member', 'member.member_id', '=', 'record.member_id');
        if($pid != 0)
            $res = $res->where('record.project_id', $pid);
        if($mid != 0)
            $res = $res->where('record.member_id', $mid);
        if($etime != 0 && $stime != 0)
            $res = $res->where([['record_time', '>=', $stime],['record_time', '<=', $etime] ]);

        $res =  $res->get();

        return response()->json([
            'status' => true,
            'res' => $res,
        ]);
    }

    public function searchPage(){
        return view('search', [
            'menu' => 'search'
        ]);
    }
}