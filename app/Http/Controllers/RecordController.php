<?php
/**
 * Created by PhpStorm.
 * User: 南宫悟
 * Date: 2018/2/9
 * Time: 10:08
 */

namespace App\Http\Controllers;


use App\Model\PeopleModel;
use App\Model\ProjectModel;
use App\Model\RecordModel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Storage;

/**
 * Class RecordController
 * @package App\Http\Controllers
 * 项目的核心类，用于处理项目的进度数据，协调员工信息与项目信息
 */
class RecordController extends Controller
{
    /**
     * @param Request $request
     * @param int $project_id
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     * 显示进度录入界面
     */
    public function index(Request $request, $project_id = 0)
    {
        LogController::insertLog("进度录入界面切换", $request);

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

    /**
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     * 添加一组进度数据
     */
    public function insert(Request $request)
    {
        LogController::insertLog("新增进度", $request);

        $project_id = $request->get('project_id');
        $data = $request->get('data');
        $date = $request->get('date');
        $args = [];
        $pt1 = 0.0;
        $pt2 = 0.0;
        $pt3 = 0.0;

        foreach ($data as $row) {
            $pt1 += (float)$row[1];
            $pt2 += (float)$row[2];
            $pt3 += (float)$row[1] + (float)$row[2];
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

    /**
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     * 查询进度数据，
     */
    public function search(Request $request)
    {
        LogController::insertLog("查询进度", $request);

        $res = $this->getSearchResult($request);

        if($res === false) {
            return response()->json([
                'status' => false,
                'info' => '时间错误',
            ]);
        } else {
            $members = [];
            foreach ($res[1] as $name=>$row) {
                $members[] = [$name, $row[0], $row[1], $row[2]];
            }
            return response()->json([
                'status' => true,
                'res' => $res[0],
                'members' =>  $members,
            ]);
        }
    }

    /**
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     * 显示搜索界面
     */
    public function searchPage(){
        return view('search', [
            'menu' => 'search'
        ]);
    }

    /**
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     * 修改进度数据，管理员修改不限，普通用户目前修改登记日期三天内的数据
     * 并非插入数据的时间
     */
    public function update(Request $request)
    {
        $record_id = $request->get('record_id');
        $pt1 = $request->get('pt1');
        $pt2 = $request->get('pt2');
        $content = $request->get('content');
        $record_time = strtotime($request->get('record_time'));

        if (($pt1 == 0 && $pt2 == 0) ||($pt1 != 0 && $pt2 != 0) || strlen($content) == 0 || $record_time == 0)
            return response()->json(
                ['status' => false]
            );

        $rec = RecordModel::select('record_time', 'project_id')
            ->where('record_id', $record_id)->first();

        if (Session::get('privilege') == 0) {
            if ($rec->record_time + 259200 < strtotime(date('Y-m-d'))) {
                return response()->json(
                    [
                        'status' => false,
                        'info' => '该记录现在已经不能修改了'
                    ]
                );
            }
        }

        RecordModel::where('record_id', $record_id)->update([
           'content' => $content,
           'record_time' => $record_time,
            'project_total1' => (float)$pt1,
            'project_total2' => (float)$pt2,
        ]);

        return response()->json([
            'status' => true,
        ]);
    }

    /**
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     * 删除进度数据
     * 管理员不限制，普通用户仅能删除三年之内
     */
    public function del(Request $request)
    {
        $record_id = $request->get('record_id');

        $rec = RecordModel::select('record_time', 'project_id')
            ->where('record_id', $record_id)->first();

        if (Session::get('privilege') == 0) {
            if ($rec->record_time + 259200 < strtotime(date('Y-m-d'))) {
                return response()->json(
                    [
                        'status' => false,
                        'info' => '该记录您目前无法删除！'
                    ]
                );
            }
        }

        RecordModel::destroy($record_id);

        return response()->json([
            'status' => true,
        ]);

    }

    /**
     * @param Request $request
     * @throws \PHPExcel_Exception
     * @throws \PHPExcel_Reader_Exception
     * @throws \PHPExcel_Writer_Exception
     *
     * 导出进度信息
     */
    public function export(Request $request)
    {
        LogController::insertLog("导出查询结果", $request);

        $data = $this->getSearchResult($request);
        $res = $data[0];
        $members = $data[1];
        if($res !== false) {
            $excel = new \PHPExcel();

            $excel->getDefaultStyle()->getAlignment()
                ->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
            $excel->getDefaultStyle()->getAlignment()
                ->setVertical(\PHPExcel_Style_Alignment::VERTICAL_CENTER);

            $excel->getProperties()->setCreator("PHPExcel")
                ->setTitle("记录结果表")
                ->setSubject("记录结果表");

            $p = $excel->setActiveSheetIndex(0);
            $p->setCellValue("A1", "记录结果")
                ->setCellValue("A2", "记录编号")
                ->setCellValue("B2", "日期")
                ->setCellValue("C2", "姓名")
                ->setCellValue("D2", "项目名称")
                ->setCellValue("E2", "工作内容")
                ->setCellValue("F2", "计量总工")
                ->setCellValue("G2", "综合总工");
            $p->setTitle("记录结果");

            $excel->getActiveSheet()->mergeCells('A1:G1');

            $excel->getActiveSheet()->getColumnDimension('A')->setWidth(10);
            $excel->getActiveSheet()->getColumnDimension('B')->setWidth(10);
            $excel->getActiveSheet()->getColumnDimension('C')->setWidth(10);
            $excel->getActiveSheet()->getColumnDimension('D')->setWidth(20);
            $excel->getActiveSheet()->getColumnDimension('E')->setWidth(40);
            $excel->getActiveSheet()->getColumnDimension('F')->setWidth(10);
            $excel->getActiveSheet()->getColumnDimension('G')->setWidth(10);

            $borderStyle = [
                'borders' => array(
                    'allborders' => array(
                        'style' => \PHPExcel_Style_Border::BORDER_THIN,
                    ),
                ),
            ];
            $p->getStyle("A1:G1")->applyFromArray($borderStyle);
            $p->getStyle("A2:G2")->applyFromArray($borderStyle);
            $i = 3;
            foreach ($res as $row) {
                $p->getStyle("A".$i.":G".$i)->applyFromArray($borderStyle);
                $p->setCellValue("A" . $i, $row->record_id);
                $p->setCellValue("B" . $i, date('Y-m-d', $row->record_time));
                $p->setCellValue("C" . $i, $row->member_name);
                $p->getStyle("D".$i)->getAlignment()->setWrapText(true);
                $p->setCellValue("D" . $i, $row->project_name);
                $p->getStyle("E".$i)->getAlignment()->setWrapText(true);
                $p->setCellValue("E" . $i, $row->content);
                $p->setCellValue("F" . $i, $row->project_total1);
                $p->setCellValue("G" . $i, $row->project_total2);
                $i++;
            }
            $excel->addSheet(new \PHPExcel_Worksheet($excel, "员工汇总"));
            $p = $excel->setActiveSheetIndex(1);
            $p->setCellValue("A1", "员工工时汇总")
                ->setCellValue("A2", "员工姓名")
                ->setCellValue("B2", "计量总工")
                ->setCellValue("C2", "综合总工")
                ->setCellValue("D2", "工日合计");

            $excel->getActiveSheet()->mergeCells('A1:D1');

            $excel->getActiveSheet()->getColumnDimension('A')->setWidth(10);
            $excel->getActiveSheet()->getColumnDimension('B')->setWidth(10);
            $excel->getActiveSheet()->getColumnDimension('C')->setWidth(10);
            $excel->getActiveSheet()->getColumnDimension('D')->setWidth(20);

            $borderStyle = [
                'borders' => array(
                    'allborders' => array(
                        'style' => \PHPExcel_Style_Border::BORDER_THIN,
                    ),
                ),
            ];
            $p->getStyle("A1:D1")->applyFromArray($borderStyle);
            $p->getStyle("A2:D2")->applyFromArray($borderStyle);
            $i = 3;
            foreach ($members as $name => $row) {
                $p->getStyle("A".$i.":D".$i)->applyFromArray($borderStyle);
                $p->setCellValue("A" . $i, $name);
                $p->setCellValue("B" . $i, $row[0]);
                $p->setCellValue("C" . $i, $row[1]);
                $p->setCellValue("D" . $i, $row[2]);
                $i++;
            }
            $excel->setActiveSheetIndex(0);

            header('Content-Type: application/vnd.ms-excel');
            header('Content-Disposition: attachment;filename="记录结果表.xls"');
            header('Cache-Control: max-age=0');
            $objWriter = \PHPExcel_IOFactory::createWriter($excel, 'Excel5');
            $objWriter->save('php://output');
        }
    }

    /**
     * @param Request $request
     * @return array|bool
     * 获得搜索结果，公共函数，被导出和搜索界面处同时调用接收时间、用户、项目参数
     * 返回两部分结果、一个是进度数据，一个是按照员工分类以后的工时数据
     */
    protected function getSearchResult(Request $request)
    {
        $mid = $request->get('member_id', 0);
        $pid = $request->get('project_id', 0);
        $stime = $request->get('stime', strtotime(date('Y-m-d')));
        $etime = $request->get('etime', strtotime(date('Y-m-d')));

        if($etime < $stime) {
            return false;
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

        $info = $res->get();

        /**
         * 统计员工信息
         */
        $members = [];
        foreach ($info as $row) {
            if (!isset($members[$row->member_name])) {
                $members[$row->member_name] = [0, 0, 0];
            }
            $members[$row->member_name][0] += $row->project_total1;
            $members[$row->member_name][1] += $row->project_total2;
            $members[$row->member_name][2] += $row->project_total1 + $row->project_total2;
        }
        return [$info, $members];
    }

    /**
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     * @throws \PHPExcel_Exception
     * 导入进度数据
     */
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

                $val = $p->getCellByColumnAndRow(1, $i)->getValue(); //登记时间
                $val = str_replace('.', '-', $val);
                $val = str_replace('/', '-', $val);
                if(strtotime($val) == 0)
                    $errors .= "第{$i}行，第B列登记时间错误; ";
                else
                    $tmp[] = strtotime($val);

                $val = $p->getCellByColumnAndRow(2, $i)->getValue(); //员工名称
                if(is_null($val) || strlen($val) == 0)
                    $errors .= "第{$i}行，第C列员工名称错误; ";
                else
                    $tmp[] = $val;

                $val = $p->getCellByColumnAndRow(3, $i)->getValue(); //项目名称
                if(is_null($val) || strlen($val) == 0)
                    $errors .= "第{$i}行，第D列项目名称错误; ";
                else
                    $tmp[] = $val;

                $val = $p->getCellByColumnAndRow(4, $i)->getValue(); //完成工作
                if(is_null($val) || strlen($val) == 0)
                    $errors .= "第{$i}行，第E列完成工作错误; ";
                else
                    $tmp[] = $val;

                $val1 = $p->getCellByColumnAndRow(5, $i)->getValue(); //计量总工
                if(is_null($val1) || !is_numeric($val1))
                    $errors .= "第{$i}行，第F列计量总工错误; ";
                else
                    $tmp[] = $val1;

                $val2 = $p->getCellByColumnAndRow(6, $i)->getValue(); //综合总工
                if(is_null($val2) || !is_numeric($val2))
                    $errors .= "第{$i}行，第G列综合总工错误; ";
                else
                    $tmp[] = $val2;

                //不能同时出现
                if($val1 != 0 && $val2 != 0) {
                    $errors .= "第{$i}行，第F, G列计量总工和综合总工不能同时填写，另一个必须为0; ";
                    $tmp = [];
                }

                if(count($tmp) == 7)
                    $data[] = $tmp;
                else {

                    //删除文件
                    unlink($path);
                    return response()->json([
                        'status' => false,
                        'info' => $errors,
                    ]);
                }
            }

            unlink($path);

            $mem_names = [];
            $pro_names = [];

            foreach ($data as $row) {
                $mem_names[] = $row[2];
                $pro_names[] = $row[3];
            }
            $mem_names = array_unique($mem_names);
            $pro_names = array_unique($pro_names);

            $res = PeopleModel::select('member_name', 'member_id')->get();
            $insertArg = [];
            foreach ($mem_names as $name) {
                $find = false;
                foreach ($res as $row) {
                    if($name == $row->member_name) {
                        $peoMap[$row->member_name] = $row->member_id;
                        $find = true;
                    }
                }
                if($find == false) {
                    $insertArg[] = [
                        'member_name' => $name,
                        'short_name' => getFirstChars($name),
                        'department' => ' ',
                        'created_at' => date('Y-m-d H:i:s'),
                        'updated_at' => date('Y-m-d H:i:s'),
                    ];
                }
            }
            if(count($insertArg) != 0)
                foreach ($insertArg as $arg)
                    $peoMap[$arg['member_name']] = PeopleModel::insertGetId($arg);

            $res = ProjectModel::select('project_name', 'project_id')->get();
            $insertArg = [];
            foreach ($pro_names as $name) {
                $find = false;
                foreach ($res as $row) {
                    if($name == $row->project_name) {
                        $find = true;
                        $proMap[$row->project_name] = $row->project_id;
                    }
                }
                if($find == false) {
                    $insertArg[] = [
                        'project_name' => $name,
                        'short_name' => getFirstChars($name),
                        'created_at' => date('Y-m-d H:i:s'),
                        'updated_at' => date('Y-m-d H:i:s'),
                    ];
                }
            }
            if(count($insertArg) != 0)
                foreach ($insertArg as  $arg)
                    $proMap[$arg['project_name']] = ProjectModel::insertGetId($arg);

            $insertArg = [];

            foreach($data as $row) {
                $insertArg[] = [
                    'project_id' => $proMap[$row[3]],
                    'member_id' => $peoMap[$row[2]],
                    'content' => $row[4],
                    'project_total1' => $row[5],
                    'project_total2' => $row[6],
                    'record_time' => $row[1],
                    'created_at' => date('Y-m-d H:i:s'),
                    'updated_at' => date('Y-m-d H:i:s'),
                ];
            }

            RecordModel::insert($insertArg);

            $len = count($insertArg);
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
 * SELECT project_id, project_name FROM project WHERE project_id = ?
 *
 * INSERT INTO record (project_id, member_id, project_total1, project_total2, content, created_at, record_time)
 *      VALUES(?,?,?,?,?,?)
 *
 * SELECT record_id, content, record_time, record.project_total1, record.project_total2, project_name, member_name
 *      FROM record LEFT JOIN project USING(project_id)
 *      LEFT JOIN member USING(member_id)
 *      WHERE record.project_id = ? AND record.member_id = ? AND record_time >= ? record_time <= ?
 *
 * SELECT record_time, project_id FROM record WHERE record_id = ?
 *
 * UPDATE record SET content = ?, record_time = ?, project_total1 = ?, project_total2 = ?
 *      WHERE record_id = ?
 *
 * SELECT record_time, project_id WHERE record_id = ?
 */