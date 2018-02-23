<?php
/**
 * Created by PhpStorm.
 * User: 南宫悟
 * Date: 2018/2/8
 * Time: 10:55
 */

namespace App\Http\Controllers;


use App\Model\PeopleModel;
use Symfony\Component\HttpFoundation\Request;


class PeopleController extends Controller
{
    public function index(Request $request)
    {
        return view('peopleManager', [
            'menu' => 'peopleManager'
        ]);
    }

    public function get(Request $request)
    {
        LogController::insertLog("显示参与人员信息", $request);

        $pms = config('web.peoManagerPageMax');
        $currentPage = $request->get('currentPage', 1);
        $total = PeopleModel::count();
        $res = PeopleModel::select('member_id', 'member_name', 'department', 'created_at', 'updated_at')
            ->offset(($currentPage - 1) * $pms)->limit($pms)->get();
        $data = [];
        foreach ($res as $row) {
            $tmp = [];
            $tmp[] = $row->member_id;
            $tmp[] = $row->member_name;
            $tmp[] = $row->department;
            $tmp[] = $row->created_at->toDateString();
            $tmp[] = $row->updated_at->toDateString();
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
        LogController::insertLog("删除指定参与人员", $request);

        $ids = $request->get('ids');
        $row = PeopleModel::destroy($ids);
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
        LogController::insertLog("修改并保存指定参与人员", $request);

        $infos = $request->get('infos');
        foreach ($infos as $pro) {
            $c = PeopleModel::where([
                ['member_id', '!=', $pro[0]],
                ['member_name', $pro[1]]
            ])->count();
            if($c == 0) {
                PeopleModel::where('member_id', $pro[0])->update([
                    'member_name' => $pro[1],
                    'department' => $pro[2],
                    'short_name' => getFirstChars($pro[1])
                ]);
            }
        }

        return response()->json([
            'status' => true,
        ]);
    }

    public function add(Request $request)
    {
        LogController::insertLog("新增一个参与人员", $request);

        $info = $request->get('info');

        $errors = [];
        if(!isset($info[0]) || strlen($info[0]) == 0)
            $errors[] = ['0', '姓名不能为空'];
        else {
            if(PeopleModel::where('member_name', $info[0])->count() != 0)
                $errors[] = ['0', '姓名重复'];
        }

        if(!isset($info[1]) || strlen($info[1]) == 0)
            $errors[] = ['1', '部门名称不能为空'];
        if(empty($errors)) {
            $peo = new PeopleModel();
            $peo->member_name = $info[0];
            $peo->department = $info[1];
            $peo->short_name = getFirstChars($info[0]);
            $peo->save();
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
        LogController::insertLog("搜索参与人员", $request);

        if($request->getMethod() == 'GET') {
            return view('peopleManager', [
                'menu' => 'peopleManager',
                'name' => $request->get('name')
            ]);
        }
        $tmp = $request->get('name', '');
        $data = [];

        if(mb_strlen($tmp) == strlen($tmp)) { //含有UTF8编码
            $key = strtoupper($tmp);
            $res = PeopleModel::select('member_id', 'member_name', 'short_name', 'department', 'created_at', 'updated_at')
                ->where('short_name', 'like', '%'.$key.'%')->get();
        } else {
            $res = PeopleModel::select('member_id', 'member_name', 'department', 'created_at', 'updated_at')
                ->where('member_name', 'like', '%'.$tmp.'%')->get();
        }

        foreach ($res as $row) {
            $data[] = [
                $row->member_id,
                $row->member_name,
                $row->department,
                $row->created_at->toDateString(),
                $row->updated_at->toDateString(),
            ];
        }
        return response()->json([
            'status' => true,
            'data' => $data,
        ]);
    }

    public function getList(Request $request)
    {
        LogController::insertLog("获取全部参与人员列表", $request);

        $res = PeopleModel::select('member_id', 'member_name', 'short_name')->get();
        $data = [];
        foreach($res as $row) {
            $data[] = [
              $row->member_name,
              $row->member_id,
              $row->short_name,
            ];
        }
        return response()->json([
            'status' => true,
            'data' => $data,
        ]);
    }

    public function export(Request $request)
    {
        LogController::insertLog("导出参与人员信息", $request);


        $excel = new \PHPExcel();
        $excel->getProperties()->setCreator("PHPExcel")
            ->setTitle("员工信息表")
            ->setSubject("信息表");
        $p = $excel->setActiveSheetIndex(0);
        $p->setCellValue("A1", "员工信息表");
        $p->setCellValue("A2", "编号")
            ->setCellValue("B2", "姓名")
            ->setCellValue("C2", "部门号")
            ->setCellValue("D2", "添加时间")
            ->setCellValue("E2", "上次修改时间");

        $excel->getActiveSheet()->getColumnDimension('D')->setWidth(18);
        $excel->getActiveSheet()->getColumnDimension('E')->setWidth(18);
        $excel->getActiveSheet()->mergeCells('A1:E1');
        $excel->getActiveSheet()->getStyle("A1")->applyFromArray(
          [
              'alignment' => [
                  'horizontal' => \PHPExcel_Style_Alignment::HORIZONTAL_CENTER
                ]
          ]
        );

        $res = PeopleModel::get();

        $i = 3;
        foreach ($res as $row) {
            $p->setCellValue("A".$i, $row->member_id)
                ->setCellValue("B".$i, $row->member_name)
                ->setCellValue("C".$i, $row->department)
                ->setCellValue("D".$i, $row->created_at)
                ->setCellValue("E".$i, $row->updated_at);
            $i++;
        }


        header('Content-Type: application/vnd.ms-excel');
        header('Content-Disposition: attachment;filename="员工信息表.xls"');
        header('Cache-Control: max-age=0');
        $objWriter = \PHPExcel_IOFactory::createWriter($excel, 'Excel5');
        $objWriter->save('php://output');
    }
}