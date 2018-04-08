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

    //连接数据库
    protected function getDoc($obj)
    {
        return DB::connection('mongodb')->collection($obj);
    }

    //一天的毫秒数
    public $day_time = 86400000;

    //接口跟目录
    public $port_path = 'http://192.168.9.251:8080/sy_gm/';

    /*
     *  调用远程接口完成部分工作
     *  第一步做的是登录验证
     * */
    //远程登录验证函数
    public function login_remote()
    {
        $url = $this->port_path . 'login.do?userName=1&userPass=2';
        $data = $this->curl($url);

        return $data;
    }

    //查询服务器ID
    public function getServer()
    {
        if (session()->has('server')) {
            return session()->get('server');
        } else {
            $sessionId = $this->login_remote();
            $url = $this->port_path . 'getLogicServer.do';
            $data = $this->curl($url, '', "JSESSIONID=" . $sessionId);
            $data = json_decode($data, true);

            $server = array_combine(array_column($data, 'serverId'), array_column($data, 'serverName'));
            ksort($server);

            session()->put('server', $server);

            return $server;
        }
    }

    //查询渠道ID
    public function getPid()
    {
        if (session()->has('pid')) {
            return session()->get('pid');
        } else {
            $sessionId = $this->login_remote();
            $url = $this->port_path . 'getPlatform.do';
            $data = $this->curl($url, '', "JSESSIONID=" . $sessionId);
            $data = json_decode($data, true);

            $pid = array_combine(array_column($data, 'id'), array_column($data, 'channel'));
            ksort($pid);

            session()->put('pid', $pid);
            return $pid;
        }
    }

    //查询管理员权限
    public function getJurisdiction()
    {
        if (session()->has('jurisdiction')) {
            return session()->get('jurisdiction');
        } else {
            $data = DB::table('t_group')->select('id', 'name')->get()->toArray();
            session()->put('jurisdiction', $data);

            return $data;
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
        $start = strtotime(date('Y-m-d', time())) * 1000;

        //时间点
        switch (request()->get('option-date')) {
            case 2://本周
                $end = time() * 1000;
                $start = strtotime(date('Y-m-d', time())) * 1000 - $this->day_time * 6;
                break;
            case 3://本月
                $end = time() * 1000;
                $start = strtotime(date('Y-m-d', time())) * 1000 - $this->day_time * 29;
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

    /**
     * @param $url 请求网址
     * @param bool $params 请求参数
     * @param int $ispost 请求方式
     * @param int $https https协议
     * @return bool|mixed
     */
    public function curl($url, $params = false, $cookie = '', $ispost = 0, $https = 0)
    {
        $httpInfo = array();
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_1);
        curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 10.0; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/41.0.2272.118 Safari/537.36');
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 30);
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_COOKIE, $cookie);
        if ($https) {
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE); // 对认证证书来源的检查
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE); // 从证书中检查SSL加密算法是否存在
        }
        if ($ispost) {
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $params);
            curl_setopt($ch, CURLOPT_URL, $url);
        } else {
            if ($params) {
                if (is_array($params)) {
                    $params = http_build_query($params);
                }
                curl_setopt($ch, CURLOPT_URL, $url . '?' . $params);
            } else {
                curl_setopt($ch, CURLOPT_URL, $url);
            }
        }

        $response = curl_exec($ch);

        if ($response === FALSE) {
            return false;
        }
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $httpInfo = array_merge($httpInfo, curl_getinfo($ch));
        curl_close($ch);
        return $response;
    }

    //根据某一字段去重二位数组
    function array_unset_tt($arr, $key)
    {
        $res = array();
        foreach ($arr as $value) {
            if (isset($res[$value[$key]])) {
                unset($value[$key]);
            } else {
                $res[$value[$key]] = $value;
            }
        }
        return $res;
    }
}
