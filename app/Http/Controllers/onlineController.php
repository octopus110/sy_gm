<?php

namespace App\Http\Controllers;

use DB;
use Illuminate\Support\Facades\Storage;

class onlineController extends Controller
{
    public function total()//总实时在线 实时服务器容量
    {
        $data = $this->getDoc('log_coll_game_real_data_log')->select(
            'serverId',
            'onlineUsers',
            'offlineCacheSize',
            'logTime'
        )->orderBy('id', 'desc')->first();

        return view('online.total', ['data' => $data, 'path' => $this->getPath()]);
    }

    //每日最高在线、平均在线
    public function maxaverage()
    {
        if (request()->status == 2) {//导出exl
            $data = assistController::dataFile('read', 'maxaverage.txt');

            $head = [['日期', '最高在线', '平均在线', '最高在线发生时刻', '平高比（平均在线/最高在线）']];

            $body = [];
            foreach ($data as $k => $v) {
                $tmp = array_values($v);
                array_unshift($tmp, $k);
                $body[] = $tmp;
            }

            $data = array_merge($head, $body);

            assistController::exportExcel('每日最高在线/平均在线', $data);

            return;
        } elseif (request()->status == 3) {//图像化显示
            $data = assistController::dataFile('read', 'maxaverage.txt');

            $tmp_key = array_keys($data);
            $graph['x'] = '';
            foreach ($tmp_key as $v) {
                $graph['x'] .= "'" . $v . "'" . ',';
            }
            $graph['x'] = rtrim($graph['x'], ',');
            
            $max_onlineuser = implode(',', array_column($data, 'max_online_user'));
            $max_onlineuser = "{'name':'最高在线',data:[" . $max_onlineuser . "]}";
            $avg_onlineuser = implode(',', array_column($data, 'avg_onlineuser'));
            $avg_onlineuser = "{'name':'平均在线',data:[" . $avg_onlineuser . "]}";

            $graph['y'] = $max_onlineuser . ',' . $avg_onlineuser;
            $graph['title'] = '每日最高在线、平均在线';
            $graph['unit_text'] = '人数';
            $graph['x_title'] = '时间';


            return view('graph.line', ['data' => $graph]);
        }

        $data_log = $this->getDoc('log_coll_game_real_data_log')->select(
            'onlineUsers',
            'logTime'
        )
            ->where('logTime', '>=', $this->getTime()[0])
            ->where('logTime', '<=', $this->getTime()[1])
            ->orderBy('logTime')
            ->get()->toArray();

        $data_log = $this->rearrayToTime($data_log, 'logTime');//按时间分组

        $data = [];
        foreach ($data_log as $k => $v) {
            $onlineuser = array_column($v, 'onlineUsers');

            if (!empty($onlineuser)) {
                $max_onlineuser = max($onlineuser);
                $max_onlineuser_time = date('Y-m-d H:i:s', $v[array_search($max_onlineuser, $onlineuser)]['logTime'] / 1000);
                $avg_onlineuser = round(array_sum($onlineuser) / count($onlineuser), 4);
                $than = round($avg_onlineuser / $max_onlineuser, 4);

                $data[$k]['max_online_user'] = $max_onlineuser;
                $data[$k]['max_onlineuser_time'] = $max_onlineuser_time;
                $data[$k]['avg_onlineuser'] = $avg_onlineuser;
                $data[$k]['than'] = $than;
            } else {
                $data[$k]['max_online_user'] = 0;
                $data[$k]['max_onlineuser_time'] = 0;
                $data[$k]['avg_onlineuser'] = 0;
                $data[$k]['than'] = 0;
            }
        }

        //把数据存储到文件中 方便导出和画图形
        assistController::dataFile('save', 'maxaverage.txt', $data);

        return view('online.ma', ['data' => $data, 'start' => $this->getTime()[0], 'end' => $this->getTime()[1]]);
    }

    //DAU和登陆次数
    public function dau()
    {
        //登录游戏的用户量
        $login_data = $this->getDoc('log_coll_login')
            ->select('userId')
            ->where('logTime', '>=', $this->getTime()[0])
            ->where('logTime', '<=', $this->getTime()[1])
            ->get();

        //去重登录游戏的用户量
        $login_data_distinct = $this->getDoc('log_coll_login')
            ->distinct('userId')
            ->where('logTime', '>=', $this->getTime()[0])
            ->where('logTime', '<=', $this->getTime()[1])
            ->get();

        //创建角色数量量
        $create_data = $this->getDoc('log_coll_create_role')
            ->distinct('userId')
            ->where('logTime', '>=', $this->getTime()[0])
            ->where('logTime', '<=', $this->getTime()[1])
            ->get()->toArray();

        $active = 0;
        foreach ($login_data_distinct as $v) {
            if (!in_array($v, $create_data)) {
                $active++;
            }
        }

        $data['active'] = $active;
        $data['login_sum'] = $login_data->count();
        $data['login_avg'] = $data['login_sum'] == 0 ? 0 : round($data['login_sum'] / $login_data_distinct->count(), 3);
        $data['time'] = [
            'start' => date('Y/m/d H:i:s', $this->getTime()[0] / 1000),
            'end' => date('Y/m/d H:i:s', $this->getTime()[1] / 1000),
        ];

        return view('online.dau', ['data' => $data]);
    }

    //每日在线人数时段分布
    public function timedistribution()
    {
        $data_log = $this->getDoc('log_coll_game_real_data_log')->select(
            'onlineUsers',
            'logTime'
        )
            ->where('logTime', '>=', $this->getTime()[0])
            ->where('logTime', '<=', $this->getTime()[1])
            ->orderBy('logTime', 'asc')
            ->get()->toArray();

        $len = count($data_log);
        for ($i = 0; $i < $len; $i++) {
            $data_log[$i]['logTime'] = "'" . date('Y - m - d H:i:s', $data_log[$i]['logTime'] / 1000) . "'";
        }

        $data['x'] = implode(',', array_column($data_log, 'logTime'));
        $data['y'] = implode(',', array_column($data_log, 'onlineUsers'));

        return view('online.timedistribution', ['data' => $data, 'start' => $this->getTime()[0], 'end' => $this->getTime()[1]]);
    }

    //平均在线时长区间分布
    public function lengthdistribution()
    {
        //登录游戏的用户量
        $login_data = $this->getDoc('log_coll_login')
            ->select('logTime', 'userId')
            ->where('logTime', '>=', $this->getTime()[0])
            ->where('logTime', '<=', $this->getTime()[1])
            ->get()->toArray();

        $login_userId = array_column($login_data, 'userId');

        //退出游戏 不加结束时间
        $loginoff_data = $this->getDoc('log_coll_logoff')
            ->select('logTime', 'userId')
            ->where('logTime', '>=', $this->getTime()[0])
            ->whereIn('userId', $login_userId)
            ->get()->toArray();

        $login_data = $this->rearrayToTime($login_data, 'logTime');//按时间分组


        $loginoff_userId = array_column($loginoff_data, 'userId');
        $loginoff_logTime = array_column($loginoff_data, 'logTime');

        foreach ($login_data as $k => $v) {
            //在线总时长 在线次数
            $duration = $time = 0;
            $area_time = [
                'zero' => 0,
                'ten' => 0,
                'thirty' => 0,
                'sixty' => 0,
                'moreTime' => 0
            ];

            foreach ($v as $value) {
                $index = array_search($value['userId'], $loginoff_userId);

                if ($index !== false) {
                    $onlint_time = abs($loginoff_logTime[$index] - $value['logTime']);
                    $duration += $onlint_time;
                    $time++;

                    if ($onlint_time > 0 && $onlint_time <= 600000) {
                        $area_time['ten']++;
                    } elseif ($onlint_time > 600000 && $onlint_time <= 1800000) {
                        $area_time['thirty']++;
                    } elseif ($onlint_time > 1800000 && $onlint_time <= 3600000) {
                        $area_time['sixty']++;
                    } else {
                        $area_time['moreTime']++;
                    }
                }

                unset($loginoff_userId[$index]);
            }

            $data[$k]['duration'] = $duration == 0 ? 0 : round($duration / 60000, 3);
            $data[$k]['avg_duration'] = $duration == 0 ? 0 : round($duration / $time / 60000, 3);
            $data[$k]['onlint_time'] = $area_time;

        }

        return view('online.lengthdistribution', ['data' => $data, 'start' => $this->getTime()[0], 'end' => $this->getTime()[1]]);
    }

    public function frequency()//平均单次在线时长
    {
        //在时间区间内登录的用户
        $login_data = $this->getDoc('log_coll_login')
            ->select('logTime', 'userId')
            ->where('logTime', '>=', $this->getTime()[0])
            ->where('logTime', '<=', $this->getTime()[1])
            ->get()->toArray();

        $login_userId = array_column($login_data, 'userId');

        //查询这些用户退出的时间
        $loginoff_data = $this->getDoc('log_coll_logoff')
            ->select('logTime', 'userId')
            ->where('logTime', '>=', $this->getTime()[0])
            ->where('logTime', '<=', $this->getTime()[1])
            ->whereIn('userId', $login_userId)
            ->get()->toArray();

        $data = [
            'time' => 0,  //在线次数
            'mistiming' => 0, //总在线时长
        ];

        foreach ($loginoff_data as $k => $v) {
            foreach ($login_data as $v0) {
                if ($v['userId'] == $v0['userId']) {
                    $data['time']++;
                    $data['mistiming'] += abs($v0['logTime'] - $v['logTime']);
                }
            }
        }


        return view('online.frequency', ['data' => $data, 'start' => $this->getTime()[0], 'end' => $this->getTime()[1]]);
    }
}