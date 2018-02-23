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

class RecordController extends Controller
{
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
        $p_etime = $p_etime = (strtotime($date));

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

        $p = ProjectModel::select('project_etime')
            ->where('project_id', $project_id)->first();

        ProjectModel::where('project_id', $project_id)->update([
           'project_etime' => max($p->project_etime,$p_etime),
        ]);

        return response()->json([
            'status' => true,
            'ids' => $res
        ]);

    }

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
            return response()->json([
                'status' => true,
                'res' => $res,
            ]);
        }
    }

    public function searchPage(){
        return view('search', [
            'menu' => 'search'
        ]);
    }

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
        $p = ProjectModel::select('project_etime')
            ->where('project_id', $rec->project_id)->first();

        ProjectModel::where('project_id', $rec->project_id)->update([
            'project_etime' => max($p->project_etime,$record_time),
        ]);

        return response()->json([
            'status' => true,
        ]);
    }

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
        $total = RecordModel::selectRaw('SUM(project_total1) AS pt1, SUM(project_total2) AS pt2')
            ->where('project_id', $rec->project_id)->first();
        ProjectModel::where('project_id', $rec->project_id)->update([
            'project_total1' => $total->pt1,
            'project_total2' => $total->pt2,
            'project_total3' => $total->pt1 + $total->pt2,
        ]);

        return response()->json([
            'status' => true,
        ]);

    }

    public function export(Request $request)
    {
        LogController::insertLog("导出查询结果", $request);

        $res = $this->getSearchResult($request);
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

            header('Content-Type: application/vnd.ms-excel');
            header('Content-Disposition: attachment;filename="记录结果表.xls"');
            header('Cache-Control: max-age=0');
            $objWriter = \PHPExcel_IOFactory::createWriter($excel, 'Excel5');
            $objWriter->save('php://output');
        }
    }

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

        return $res->get();
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
            return response()->json(
                ['status' => true, 'path' => base64_encode($path)]
            );
        } else {
            return response()->json(
                ['status' => false, 'info' => '只能使用xls或者xlsx文件，当前为'.$extension]
            );
        }
    }

    public function do_import(Request $request)
    {
        if($request->getMethod() == 'GET') {
            $path = base64_decode($request->get('_path'));
            $path = substr(__DIR__, 0, strlen(__DIR__) - 20).'storage/app/'.$path;
            $tmp = explode('.', $path);

            if(!file_exists( $path)|| count($tmp) != 2 || ($tmp[1] != 'xls' && $tmp[1] != 'xlsx')) {
                return view('do_import', [
                   'status' => false,
                   'info' => '文件已经失效，请重新上传'
                ]);
            } else {
                $reader = $tmp[1] == 'xls' ? new \PHPExcel_Reader_Excel5() : new \PHPExcel_Reader_Excel2007();
                $excel = $reader->load($path);
                $p = $excel->getActiveSheet();
                $rows = $p->getHighestDataRow();
                $cols = $p->getHighestDataColumn();
                $data = [];
                $len = ord($cols) - ord('A');
                for ($i = 1; $i <= $rows; $i++) {
                    $tmp = [];
                    for ($j = 0; $j <= $len; $j++) {
                        $val = $p->getCellByColumnAndRow($j, $i)->getValue();
                        if(!is_null($val)) {
                            $tmp[] = $val;
                        }
                    }
                    if(count($tmp) == $len + 1)
                        $data[] = $tmp;
                }
                return view('do_import', [
                   'status' => true,
                    'rows' => $rows,
                    'cols' => $cols,
                    'data' => $data,
                ]);
            }

        } else {
            $data = $request->get('data');
            $mem_names = [];
            $pro_names = [];

            foreach ($data as $row) {
                $mem_names[] = $row[2];
                $pro_names[] = $row[3];
            }
            $mem_names = array_unique($mem_names);
            $pro_names = array_unique($pro_names);

            $res = PeopleModel::select('member_name')->get();
            $insertArg = [];
            foreach ($mem_names as $name) {
                $find = false;
                foreach ($res as $row) {
                    if($name == $row->member_name) {
                        $find = true;
                    }
                }
                if($find == false) {
                    $insertArg[] = [
                        'member_name' => $name,
                        'short_name' => getFirstChars($name),
                        'department' => ''
                    ];
                }
            }
            if(count($insertArg) != 0)
                PeopleModel::insert($insertArg);

            $res = ProjectModel::select('project_name')->get();
            $insertArg = [];
            foreach ($pro_names as $name) {
                $find = false;
                foreach ($res as $row) {
                    if($name == $row->project_name) {
                        $find = true;
                    }
                }
                if($find == false) {
                    $insertArg[] = [
                        'project_name' => $name,
                        'short_name' => getFirstChars($name),
                    ];
                }
            }
            if(count($insertArg) != 0)
                PeopleModel::insert($insertArg);
        }
    }
}