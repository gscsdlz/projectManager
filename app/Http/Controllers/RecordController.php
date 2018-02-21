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

    public function export(Request $request)
    {
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
                ->setCellValue("F2", "工时");

            $excel->getActiveSheet()->mergeCells('A1:F1');

            $excel->getActiveSheet()->getColumnDimension('A')->setWidth(10);
            $excel->getActiveSheet()->getColumnDimension('B')->setWidth(10);
            $excel->getActiveSheet()->getColumnDimension('C')->setWidth(10);
            $excel->getActiveSheet()->getColumnDimension('D')->setWidth(20);
            $excel->getActiveSheet()->getColumnDimension('E')->setWidth(40);
            $excel->getActiveSheet()->getColumnDimension('F')->setWidth(10);

            $borderStyle = [
                'borders' => array(
                    'allborders' => array(
                        'style' => \PHPExcel_Style_Border::BORDER_THIN,
                    ),
                ),
            ];
            $p->getStyle("A1:F1")->applyFromArray($borderStyle);
            $p->getStyle("A2:F2")->applyFromArray($borderStyle);
            $i = 3;
            foreach ($res as $row) {
                $p->getStyle("A".$i.":F".$i)->applyFromArray($borderStyle);
                $p->setCellValue("A" . $i, $row->record_id);
                $p->setCellValue("B" . $i, date('Y-m-d', $row->record_time));
                $p->setCellValue("C" . $i, $row->member_name);
                $p->getStyle("D".$i)->getAlignment()->setWrapText(true);
                $p->setCellValue("D" . $i, $row->project_name);
                $p->getStyle("E".$i)->getAlignment()->setWrapText(true);
                $p->setCellValue("E" . $i, $row->content);
                if($row->project_total1 == 0)
                    $p->setCellValue("F" . $i, $row->project_total2);
                else
                    $p->setCellValue("F" . $i, $row->project_total1);
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
}