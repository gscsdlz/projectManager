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
use Illuminate\Support\Facades\DB;
use phpDocumentor\Reflection\Project;

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

        $res = DB::table('project')->selectRaw("project.project_id, project_name, project_attr, project_stime, SUM(record.project_total1) AS t1, SUM(record.project_total2) AS t2, MAX(record.record_time) as project_etime")
            ->leftJoin('record', 'record.project_id', '=', 'project.project_id')
            ->groupBy('project.project_id')
             ->limit($pms)->offset(($currentPage - 1) * $pms)
            ->get();
        $data = [];
        foreach ($res as $row) {
            $tmp = [];
            $tmp[] = $row->project_id;
            $tmp[] = $row->project_name;
            $jtmp = json_decode($row->project_attr, true);
            $tmp[] = is_null($jtmp[0]) ? "暂无记录" : $jtmp[0];
            $tmp[] = is_null($jtmp[1]) ? "暂无记录" : $jtmp[1];
            $tmp[] = is_null($jtmp[2]) ? "暂无记录" : $jtmp[2];
            $tmp[] = is_null($jtmp[3]) ? "暂无记录" : $jtmp[3];

            $tmp[] = round($row->t1, 2);
            $tmp[] = round($row->t2, 2);
            $tmp[] = round($row->t1 + $row->t2, 2);
            $tmp[] = date('Y-m-d', $row->project_stime);
            if($row->project_etime == 0)
                $tmp[] = "暂无记录";
            else
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
                'project_stime' => strtotime($pro[9]),
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

        if(!isset($info[5]))
            $errors[] = ['5 ', '项目开始时间为必填项'];
        else
            $stime = strtotime($info[5]);

        if(empty($errors)) {
            $pro = new ProjectModel();
            $pro->project_name = $info[0];
            $pro->project_stime = $stime;
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
            $res = DB::table('project')->selectRaw("project.project_id, project_name, project_attr, project_stime, SUM(record.project_total1) AS t1, SUM(record.project_total2) AS t2, MAX(record.record_time) as project_etime")
                ->leftJoin('record', 'record.project_id', '=', 'project.project_id')
                ->groupBy('project.project_id')
                ->where('short_name', 'like', '%' . $key . '%')->get();
        } else {
            $res = DB::table('project')->selectRaw("project.project_id, project_name, project_attr, project_stime, SUM(record.project_total1) AS t1, SUM(record.project_total2) AS t2, MAX(record.record_time) as project_etime")
                ->leftJoin('record', 'record.project_id', '=', 'project.project_id')
                ->groupBy('project.project_id')
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

            $tmp[] = round($row->t1, 2);
            $tmp[] = round($row->t2, 2);
            $tmp[] = round($row->t1 + $row->t2 , 2);
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

    public function export(Request $request)
    {
        $res = DB::table('project')->selectRaw("project.project_id, project_name, project_attr, project_stime, SUM(record.project_total1) AS t1, SUM(record.project_total2) AS t2, MAX(record.record_time) AS project_etime")
            ->leftJoin('record', 'record.project_id', '=', 'project.project_id')
            ->groupBy('project.project_id')
            ->get();

        $excel = new \PHPExcel();
        $excel->getProperties()->setCreator("PHPExcel")
            ->setTitle("项目信息表")
            ->setSubject("信息表");
        $p = $excel->setActiveSheetIndex(0);

        $p->setCellValue("A1", "项目信息表")
            ->setCellValue("A2", "编号")
            ->setCellValue("B2", "项目名称")
            ->setCellValue("C2", "建筑面积")
            ->setCellValue("D2", "层数")
            ->setCellValue("E2", "檐高")
            ->setCellValue("F2", "总造价")
            ->setCellValue("G2", "计量总工")
            ->setCellValue("H2", "综合总工")
            ->setCellValue("I2", "工日合计")
            ->setCellValue("J2", "开始时间")
            ->setCellValue("K2", "结束时间");

        $excel->getActiveSheet()->getColumnDimension('A')->setWidth(10);
        $excel->getActiveSheet()->getColumnDimension('B')->setWidth(30);
        $excel->getActiveSheet()->getColumnDimension('C')->setWidth(10);
        $excel->getActiveSheet()->getColumnDimension('D')->setWidth(10);
        $excel->getActiveSheet()->getColumnDimension('E')->setWidth(10);
        $excel->getActiveSheet()->getColumnDimension('F')->setWidth(10);
        $excel->getActiveSheet()->getColumnDimension('G')->setWidth(10);
        $excel->getActiveSheet()->getColumnDimension('H')->setWidth(10);
        $excel->getActiveSheet()->getColumnDimension('I')->setWidth(10);
        $excel->getActiveSheet()->getColumnDimension('J')->setWidth(10);
        $excel->getActiveSheet()->getColumnDimension('K')->setWidth(10);


        $excel->getActiveSheet()->mergeCells('A1:K1');
        $excel->getActiveSheet()->getStyle("A1")->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
        $borderStyle = [
            'borders' => array(
                'allborders' => array(
                    'style' => \PHPExcel_Style_Border::BORDER_THIN,
                ),
            ),
        ];
        $p->getStyle("A1:K1")->applyFromArray($borderStyle);
        $p->getStyle("A2:K2")->applyFromArray($borderStyle);
        $i = 3;
        foreach ($res as $row) {
            $p->getStyle("A".$i.":K".$i)->applyFromArray($borderStyle);

            $jtmp = json_decode($row->project_attr, true);

            $p->getStyle("B".$i)->getAlignment()->setWrapText(true);


            $p->setCellValue("A".$i, $row->project_id)
                ->setCellValue("B".$i, $row->project_name)
                ->setCellValue("C".$i, is_null($jtmp[0]) ? "暂无记录" : $jtmp[0])
                ->setCellValue("D".$i, is_null($jtmp[1]) ? "暂无记录" : $jtmp[1])
                ->setCellValue("E".$i, is_null($jtmp[2]) ? "暂无记录" : $jtmp[2])
                ->setCellValue("F".$i, is_null($jtmp[3]) ? "暂无记录" : $jtmp[3])
                ->setCellValue("G".$i, round($row->t1, 2))
                ->setCellValue("H".$i, round($row->t2, 2))
                ->setCellValue("I".$i, round($row->t1 + $row->t2, 2))
                ->setCellValue("J".$i, date('Y-m-d', $row->project_stime))
                ->setCellValue("K".$i, $row->project_etime == 0 ? "暂无记录" : date('Y-m-d', $row->project_etime));
            $i++;
        }


        header('Content-Type: application/vnd.ms-excel');
        header('Content-Disposition: attachment;filename="项目信息表.xls"');
        header('Cache-Control: max-age=0');
        $objWriter = \PHPExcel_IOFactory::createWriter($excel, 'Excel5');
        $objWriter->save('php://output');
    }

    public function import(Request $request)
    {
        if(!$request->hasFile('file') || !$request->file('file')->isValid()) {
            return response()->json(
                ['status' => false, 'info' => '文件不存在，文件上传失败']
            );
        }
        $file = $request->file('file');
        $extension = $file->getClientOriginalExtension();
        if ($extension == 'xls' || $extension == 'xlsx') {
            $path = $file->storeAs('tmp', time() . rand(0, 1000) .'.'.$extension);
            $path = substr(__DIR__, 0, strlen(__DIR__) - 20).'storage/app/'.$path;

            $reader = $extension == 'xls' ? new \PHPExcel_Reader_Excel5() : new \PHPExcel_Reader_Excel2007();
            $excel = $reader->load($path);
            $p = $excel->getActiveSheet();
            $rows = $p->getHighestDataRow();
            $cols = $p->getHighestDataColumn();

            if($cols != 'G') {
                return response()->json([
                    'status' => false,
                    'info' => "缺少字段数，列数应该是7列.请检查！"
                ]);
            }
            $data = [];
            $errors = '';
            for ($i = 1; $i <= $rows; $i++) {
                $tmp = [];
                $val = $p->getCellByColumnAndRow(0, $i)->getValue(); //编号
                $tmp[] = is_null($val) ? 1 : $val;

                $val = $p->getCellByColumnAndRow(1, $i)->getValue(); //项目名称
                if(is_null($val) || strlen($val) == 0)
                    $errors .= "第{$i}行，第B列项目名称错误; ";
                else
                    $tmp[] = $val;

                $val = $p->getCellByColumnAndRow(2, $i)->getValue(); //建筑面积
                if(is_null($val) || strlen($val) == 0)
                    $errors .= "第{$i}行，第C列建筑面积错误; ";
                else
                    $tmp[] = $val;

                $val = $p->getCellByColumnAndRow(3, $i)->getValue(); //层数
                if(is_null($val) || strlen($val) == 0)
                    $errors .= "第{$i}行，第D列层数错误; ";
                else
                    $tmp[] = $val;

                $val = $p->getCellByColumnAndRow(4, $i)->getValue(); //檐高
                if(is_null($val) || strlen($val) == 0)
                    $errors .= "第{$i}行，第E列檐高错误; ";
                else
                    $tmp[] = $val;

                $val = $p->getCellByColumnAndRow(5, $i)->getValue(); //总造价
                if(is_null($val) || strlen($val) == 0)
                    $errors .= "第{$i}行，第F列总造价错误; ";
                else
                    $tmp[] = $val;

                $val = $p->getCellByColumnAndRow(6, $i)->getValue(); //开始时间
                $val = str_replace('.', '-', $val);
                $val = str_replace('/', '-', $val);
                if(strtotime($val) == 0)
                    $errors .= "第{$i}行，第G列开始时间错误; {$val}";
                else
                    $tmp[] = strtotime($val);


                if(count($tmp) == 7)
                    $data[] = $tmp;
                else {
                    unlink($path);
                    return response()->json([
                        'status' => false,
                        'info' => $errors,
                    ]);
                }
            }

            unlink($path);

            $res = ProjectModel::select('project_name', 'project_id')->get();
            $updateArg = [];
            $insertArg = [];
            foreach ($data as $name) {
                $find = false;
                foreach ($res as $row) {
                    if($name[1] == $row->project_name) {
                        $find = true;
                        $updateArg[$row->project_id] = [
                            'project_attr' => json_encode([$name[2], $name[3], $name[4], $name[5]]),
                            'project_stime' => $name[6],
                        ];
                    }
                }
                if ($find == false) {
                    $insertArg[] = [
                        'project_name' => $name[1],
                        'short_name' => getFirstChars($name[1]),
                        'project_attr' => json_encode([$name[2], $name[3], $name[4], $name[5]]),
                        'project_stime' => $name[6],
                        'created_at' => date('Y-m-d H:i:s'),
                        'updated_at' => date('Y-m-d H:i:s'),
                    ];
                }
            }
            if (count($insertArg) != 0)
                ProjectModel::insert($insertArg);
            foreach ($updateArg as $id => $arg) {
                ProjectModel::where('project_id', $id)->update($arg);
            }

            $len = count($data);
            return response()->json([
                'status' => true,
                'info' => "操作完成，上传文件总行数：{$rows}, 实际保存行数：{$len}"
            ]);
        } else {
            return response()->json(
                ['status' => false, 'info' => '只能使用xls或者xlsx文件，当前为'.$extension]
            );
        }
    }
}