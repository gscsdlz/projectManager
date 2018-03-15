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

/**
 * Class ProjectController
 * @package App\Http\Controllers
 *
 * 项目管理类，管理项目的CURD，以及导入导出
 */
class ProjectController extends Controller
{
    /**
     * @param Request $request
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     * 显示界面
     */
    public function index(Request $request)
    {
        return view('projectManager', [
            'menu' => 'projectManager',
        ]);
    }

    /**
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     * 获取项目信息，计算得到工时信息，和项目而结束时间。
     */
    public function get(Request $request)
    {
        LogController::insertLog("获得项目信息", $request);

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

    /**
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     * 删除项目 删除失败自动提示JSON回应 配置在APP\Exceptions\Handler.php@45
     */
    public function dels(Request $request)
    {
        LogController::insertLog("删除项目", $request);

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

    /**
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     * 保存项目信息，仅能修改少量数据
     */
    public function save(Request $request)
    {
        LogController::insertLog("保存项目", $request);

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

    /**
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     * 添加项目
     */
    public function add(Request $request)
    {
        LogController::insertLog("新增一组项目文件", $request);

        $info = $request->get('info');

        $errors = [];
        if(!isset($info[0]) || strlen($info[0]) == 0)
            $errors[] = ['0', '项目名称不能为空'];
        else if(ProjectModel::where('project_name', $info[0])->count() != 0)
            $errors[] = ['0', '项目名称重复'];
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

    /**
     * @param Request $request
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\Http\JsonResponse|\Illuminate\View\View
     * 搜索项目，支持拼音简写搜索或者中文搜索，二者选一
     */
    public function search(Request $request)
    {
        LogController::insertLog("搜索项目", $request);

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

    /**
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     * 用于录入时选择项目信息，侧边栏
     */
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

    /**
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     * 用于搜索时的项目信息，包含全名和短名称
     */
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

    /**
     * @param Request $request
     * @throws \PHPExcel_Exception
     * @throws \PHPExcel_Reader_Exception
     * @throws \PHPExcel_Writer_Exception
     * 导出数据
     */
    public function export(Request $request)
    {
        LogController::insertLog("导出项目", $request);

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

    /**
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     * @throws \PHPExcel_Exception
     * 从xls、xlsx导入数据文件
     */
    public function import(Request $request)
    {
        LogController::insertLog("导入项目", $request);

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

/**
 * SQL整理
 * DELETE FROM project WHERE project_id IN (?,?);
 *
 * UPDATE project SET project_name = ?, short_name = ?,project_attr = ?, project_stime = ?, updated_at = ? WHERE project_id = ?
 *
 * INSERT INTO project (project_name, short_name, project_attr, project_stime, created_at) VALUES (?,?,?,?,?)
 *
 * SELECT project.project_id, project_name, project_attr, project_stime, SUM(record.project_total1) AS t1, SUM(record.project_total2) AS t2, MAX(record.record_time) as project_etime FROM
 *      project LEFT JOIN record USING (project_id) GROUP BY(project.project_id) WHERE
 *      short_name LIKE ? ? project_name LIKE ?
 *
 * SELECT project_id, project_name FROM project LIMIT ?, ?
 *
 * SELECT project_id, project_name, short_name FROM project WHERE 1
 *
 * SELECT COUNT(*) FROM project WHERE 1
 *
 * SELECT COUNT(*) FROM project WHERE project_name = ?
 */