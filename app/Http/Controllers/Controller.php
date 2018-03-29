<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use DB;
use Illuminate\Support\Facades\Storage;
use Maatwebsite\Excel\Facades\Excel;

class Controller extends BaseController
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;

    protected function getDoc($obj)
    {
        return DB::collection($obj);
    }

    public $day_time = 86400000;

    //查询渠道ID
    public function getPid()
    {
        if (session()->has('pid')) {
            return session()->get('pid');
        } else {
            $pid = $this->getDoc('log_coll_create_user')->distinct('pid')->get();
            session()->put('pid', $pid);
            return $pid;
        }
    }

    //查询服务器ID
    public function getServer()
    {
        if (session()->has('serverId')) {
            return session()->get('serverId');
        } else {
            $serverId = $this->getDoc('log_coll_login')->distinct('serverId')->get();
            session()->put('serverId', $serverId);
            return $serverId;
        }
    }

    //获取route路径
    public function getPath()
    {
        return explode('/', request()->path());
    }

    //构造时间数组
    public function generationTimeSeries($start, $end)
    {
        $data = [];
        do {
            $data[] = date('Y-m-d', $start / 1000);
            $start += $this->day_time;
        } while ($start < $end);

        $data[] = date('Y-m-d', $end / 1000);
        return $data;
    }

    //构造时间数组（小时）
    public function generationHourSeries($start, $end)
    {
        $one_day = 3600000;//一小时的时间戳
        $data = [];
        do {
            $data[] = date('H', $start / 1000);
            $start += $one_day;
        } while ($start < $end);

        $data[] = date('H', $end / 1000);
        return $data;
    }

    //构造时间数组年月日时
    public function generationDateHourSeries($start, $end)
    {
        $data = [];
        do {
            $data[] = date('Y-m-d H', $start / 1000);
            $start += $this->day_time;
        } while ($start < $end);

        $data[] = date('Y-m-d H', $end / 1000);
        return $data;
    }


    //获取时间
    public function getTime()
    {
        //默认查询今天
        $end = time() * 1000;
        $start = $end - $this->day_time;

        //时间点
        switch (request()->get('option-date')) {
            case 1://本天
                $end = time() * 1000;
                $start = $end - $this->day_time;
                break;
            case 2://本周
                $end = time() * 1000;
                $start = $end - $this->day_time * 7;
                break;
            case 3://本月
                $end = time() * 1000;
                $start = $end - $this->day_time * 30;
                break;
        }

        //时间段覆盖时间点
        if (request()->get('interval-date-start')) {
            $start = strtotime(request()->get('interval-date-start')) * 1000;
        }
        if (request()->get('interval-date-end')) {
            $end = strtotime(request()->get('interval-date-end')) * 1000;
        }

        return [$start, $end];
    }

    //按时间重新组合数组
    public function rearrayToTime($data, $time, $format = 'Y-m-d')
    {
        $result = [];
        foreach ($data as $v) {
            $result[date($format, $v[$time] / 1000)][] = $v;
        }

        return $result;
    }

    //按小时重新组合分组
    public function rearrayToHour($data, $time)
    {
        $result = [];
        foreach ($data as $v) {
            $result[date('H', $v[$time] / 1000)][] = $v;
        }

        return $result;
    }

    //自定义标准返回值
    public function standard_return_view($view, $data)
    {
        return view($view, [
            'data' => $data,
            'start' => $this->getTime()[0],
            'end' => $this->getTime()[1],
            'option' => request()->get('option-date') ? request()->get('option-date') : 1,
            'type' => request()->get('type-date') ? request()->get('type-date') : 1,
            'pid' => $this->getPid(),
            'select_pid' => request()->get('pid') ? request()->get('pid') : 0,
            'server' => $this->getServer(),
            'serverId' => request()->get('serverId') ? request()->get('serverId') : 0,
        ]);
    }

    //定义直线图标准返回数据
    public function discount_data($title, $x, $y, $unit_x, $unit_y)
    {
        return [
            'title' => $title,
            'x' => $x,
            'y' => $y,
            'x_title' => $unit_x,
            'unit_text' => $unit_y
        ];
    }

    //导出exl
    public function exportExcel($head, $title)
    {
        $data = $this->dataFile('read', 'data.txt');

        $head = [$head];
        $body = [];
        foreach ($data as $k => $v) {
            $tmp = array_values($v);
            array_unshift($tmp, $k);
            $body[] = $tmp;
        }
        $data = array_merge($head, $body);
        $this->exceel($title, $data);

        return;
    }

    //生成excel表格
    public function exceel($title, $cellData)
    {
        Excel::create($title, function ($excel) use ($cellData) {
            $excel->sheet('GMexcel', function ($sheet) use ($cellData) {
                $sheet->rows($cellData);
            });
        })->export('xls');

    }

    //保存数据到文件
    public function dataFile($oper, $fileName, $data = [])
    {
        switch ($oper) {
            case 'save':
                $rst = Storage::disk('local')->put($fileName, json_encode($data));
                break;
            case 'read':
                $rst = json_decode(Storage::disk('local')->get($fileName), true);
                break;
        }
        return $rst;
    }

    //后期填补数据
    public function fill(&$data, $tmp)
    {
        $this_date = $this->generationTimeSeries($this->getTime()[0], $this->getTime()[1]);//获取时间列表
        foreach ($this_date as $v) {
            if (!isset($data[$v])) {
                $data[$v] = $tmp;
            }
        }
    }
}
