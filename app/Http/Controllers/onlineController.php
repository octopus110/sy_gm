<?php

namespace App\Http\Controllers;

use DB;

class onlineController extends Controller
{
    //首页
    public function index()
    {
        $db_hand = $this->getDoc('log_coll_game_real_data_log')->select(
            'onlineUsers'
        )
            ->where('logTime', '>=', $this->getTime()[0])
            ->where('logTime', '<=', $this->getTime()[1])
            ->get()->toArray();

        $db_hand = array_column($db_hand, 'onlineUsers');

        $data['max_online'] = empty($db_hand) ? 0 : max($db_hand);

        $db_hand = $this->getDoc('log_coll_login')->select(
            'userId'
        )
            ->where('logTime', '>=', $this->getTime()[0])
            ->where('logTime', '<=', $this->getTime()[1])
            ->get()->toArray();
        $db_hand = array_column($db_hand, 'userId');
        $login = array_unique($db_hand);

        $data['login_sum'] = empty($db_hand) ? 0 : count($db_hand);

        $db_hand = $this->getDoc('log_coll_recharge')->select(
            'userId', 'rechargeRMB'
        )
            ->where('logTime', '>=', $this->getTime()[0])
            ->where('logTime', '<=', $this->getTime()[1])
            ->get()->toArray();

        $data['income'] = array_sum(array_column($db_hand, 'rechargeRMB'));
        $data['pay_user'] = array_sum(array_unique(array_column($db_hand, 'userId')));
        $data['pay_user_rat'] = $data['income'] == 0 ? 0 : round($data['income'] / $data['pay_user'], 3);
        $data['pay_login_rat'] = $data['income'] == 0 ? 0 : round($data['income'] / $data['login_sum'], 3);
        $data['pay_rat'] = count($login) == 0 ? 0 : round($data['pay_user'] / array_sum($login), 3);

        $data['login_user'] = count($login);

        $db_hand = $this->getDoc('log_coll_create_user')->select(
            'userId'
        )
            ->where('logTime', '>=', $this->getTime()[0])
            ->where('logTime', '<=', $this->getTime()[1])
            ->get()->toArray();

        $data['user_create'] = count(array_column($db_hand, 'userId'));

        $db_hand = $this->getDoc('log_coll_create_role')->distinct(
            'userId'
        )
            ->where('logTime', '>=', $this->getTime()[0])
            ->where('logTime', '<=', $this->getTime()[1])
            ->get()->toArray();

        $data['active'] = count(array_diff($login, $db_hand));


        return view('index', ['data' => $data]);
    }

    //总实时在线 实时服务器容量
    public function total()
    {
        $data = $this->getDoc('log_coll_game_real_data_log')->select(
            'serverId',
            'onlineUsers',
            'offlineCacheSize'
        )->orderBy('_id', 'desc')->limit(50)->get()->toArray();

        $tmp = [];
        foreach ($data as $v) {
            $serverId = $v['serverId'];
            if (!isset($tmp[$serverId])) {
                $tmp[$serverId] = $v;
            }
        }

        //构造参数
        $cart['x'] = implode(',', array_keys($tmp));
        $cart['y1'] = implode(',', array_column($tmp, 'onlineUsers'));
        $cart['y2'] = implode(',', array_column($tmp, 'offlineCacheSize'));

        return view('online.total', ['data' => $cart]);
    }

    //每日最高在线、平均在线
    public function maxaverage()
    {
        if (request()->status == 2) {//导出exl
            $this->exportExcel(['日期', '最高在线', '平均在线', '最高在线发生时刻', '平高比（平均在线/最高在线）'], '每日最高在线/平均在线');

            return;
        } elseif (in_array(request()->status, [3, 4, 5])) {//图像化显示
            $data = $this->dataFile('read', 'data.txt');

            $tmp_key = array_keys($data);
            $graph['x'] = '';
            foreach ($tmp_key as $v) {
                $graph['x'] .= "'" . $v . "'" . ',';
            }
            $x = rtrim($graph['x'], ',');

            switch (request()->status) {
                case 3:
                    $y = implode(',', array_column($data, 'max_online_user'));
                    $y = "{'name':'最高在线',data:[" . $y . "]}";

                    $graph = $this->discount_data('最高在线', $x, $y, '时间', '人数');
                    break;
                case 4:
                    $y = implode(',', array_column($data, 'avg_onlineuser'));
                    $y = "{'name':'平均在线',data:[" . $y . "]}";
                    $graph = $this->discount_data('平均在线', $x, $y, '时间', '人数');
                    break;
                case 5:
                    $y = implode(',', array_column($data, 'than'));
                    $y = "{'name':'高平比',data:[" . $y . "]}";
                    $graph = $this->discount_data('高平比', $x, $y, '时间', '人数');
                    break;
            }

            return view('graph.line', ['data' => $graph, 'title' => '每日最高在线、平均在线']);
        }

        $data_log = $this->getDoc('log_coll_game_real_data_log')->select(
            'onlineUsers',
            'logTime'
        )
            ->where('logTime', '>=', $this->getTime()[0])
            ->where('logTime', '<=', $this->getTime()[1]);
        if (request()->pid) {
            $data_log = $data_log->where('pid', request()->pid);
        }

        if (request()->serverId) {
            $data_log = $data_log->where('serverId', request()->serverId);
        }

        $data_log = $data_log->get();

        $data = [];
        if (request()->get('type-date') == 1) { //按日显示
            $data_log = $this->rearrayToTime($data_log, 'logTime');//按时间分组
            $this_date = $this->generationTimeSeries($this->getTime()[0], $this->getTime()[1]);//获取时间列表

            foreach ($this_date as $v) {
                if (isset($data_log[$v])) {
                    $onlineuser = array_column($data_log[$v], 'onlineUsers');

                    $max_onlineuser = max($onlineuser);
                    $max_onlineuser_time = date('Y-m-d H:i:s', $data_log[$v][array_search($max_onlineuser, $onlineuser)]['logTime'] / 1000);
                    $avg_onlineuser = round(array_sum($onlineuser) / count($onlineuser), 3);
                    $than = round($avg_onlineuser / count($onlineuser), 3);

                    $data[$v]['max_online_user'] = $max_onlineuser;
                    $data[$v]['max_onlineuser_time'] = $max_onlineuser_time;
                    $data[$v]['avg_onlineuser'] = $avg_onlineuser;
                    $data[$v]['than'] = $than;
                } else {
                    $data[$v]['max_online_user'] = 0;
                    $data[$v]['max_onlineuser_time'] = 0;
                    $data[$v]['avg_onlineuser'] = 0;
                    $data[$v]['than'] = 0;
                }
            }
        } else if (request()->get('type-date') == 2) { //按时间段显示
            $onlineuser = array_column($data_log, 'onlineUsers');
            $max_onlineuser = max($onlineuser);
            $max_onlineuser_time = date('Y-m-d H:i:s', $data_log[array_search($max_onlineuser, $onlineuser)]['logTime'] / 1000);
            $avg_onlineuser = round(array_sum($onlineuser) / count($onlineuser), 3);
            $than = round($avg_onlineuser / $max_onlineuser, 3);

            $k = date('Y-m-d', $this->getTime()[0] / 1000) . ' 至 ' . date('Y-m-d', $this->getTime()[1] / 1000);
            $data[$k]['max_online_user'] = $max_onlineuser;
            $data[$k]['max_onlineuser_time'] = $max_onlineuser_time;
            $data[$k]['avg_onlineuser'] = $avg_onlineuser;
            $data[$k]['than'] = $than;
        }

        //把数据存储到文件中 方便导出和画图形
        $this->dataFile('save', 'data.txt', $data);

        return $this->standard_return_view('online.maxaverage', $data);
    }

    //DAU和登陆次数
    public function dau()
    {
        if (request()->status == 2) {//导出exl
            $this->exportExcel(['统计时间', '活跃用户', '登录次数', '人均登录次数'], 'DAU和登录次数');
            return;
        } elseif (in_array(request()->status, [3, 4, 5])) {//图像化显示
            $data = $this->dataFile('read', 'data.txt');

            $tmp_key = array_keys($data);
            $graph['x'] = '';
            foreach ($tmp_key as $v) {
                $graph['x'] .= "'" . $v . "'" . ',';
            }
            $x = rtrim($graph['x'], ',');

            switch (request()->status) {
                case 3:
                    $y = implode(',', array_column($data, 'active'));
                    $y = "{'name':'活跃用户',data:[" . $y . "]}";
                    $graph = $this->discount_data('活跃用户', $x, $y, '时间', '人数');
                    break;
                case 4:
                    $y = implode(',', array_column($data, 'login_sum'));
                    $y = "{'name':'用户登录次数',data:[" . $y . "]}";
                    $graph = $this->discount_data('用户登录次数', $x, $y, '时间', '人数');
                    break;
                case 5:
                    $y = implode(',', array_column($data, 'login_avg'));
                    $y = "{'name':'人均登陆次数',data:[" . $y . "]}";
                    $graph = $this->discount_data('人均登陆次数', $x, $y, '时间', '人数');
                    break;
            }

            return view('graph.line', ['data' => $graph, 'title' => 'DAU和登陆次数']);
        }

        //登录游戏的用户量
        $login_data = $this->getDoc('log_coll_login')
            ->select('userId', 'logTime')
            ->where('logTime', '>=', $this->getTime()[0])
            ->where('logTime', '<=', $this->getTime()[1]);

        if (request()->pid) {
            $login_data = $login_data->where('pid', request()->pid * 1);
        }

        if (request()->serverId) {
            $login_data = $login_data->where('serverId', request()->get('serverId') * 1);
        }
        $login_data = $login_data->get()->toArray();

        //创建角色数量量
        $create_data = $this->getDoc('log_coll_create_role')
            ->select('userId', 'logTime')
            ->where('logTime', '>=', $this->getTime()[0])
            ->where('logTime', '<=', $this->getTime()[1]);

        if (request()->pid) {
            $create_data = $create_data->where('pid', request()->pid * 1);
        }

        if (request()->serverId) {
            $create_data = $create_data->where('serverId', request()->get('serverId') * 1);
        }

        $create_data = $create_data->get()->toArray();

        $data = [];
        if (request()->get('type-date') == 1) { //按日显示

            $login_data = $this->rearrayToTime($login_data, 'logTime');//按时间分组
            $create_data = $this->rearrayToTime($create_data, 'logTime');//按时间分组

            $this_date = $this->generationTimeSeries($this->getTime()[0], $this->getTime()[1]);//获取时间列表

            foreach ($this_date as $k => $v) {
                $data[$v]['active'] = $data[$v]['login_sum'] = $data[$v]['login_avg'] = 0;
                if (isset($login_data[$v])) {
                    $login_data_distinct = array_unique(array_column($login_data[$v], 'userId'));
                    $create_data_distinct = array_unique(array_column($create_data[$v], 'userId'));

                    $data[$v]['active'] = count(array_diff($login_data_distinct, $create_data_distinct));

                    $data[$v]['login_sum'] = count($login_data[$v]);
                    $data[$v]['login_avg'] = $data[$v]['login_sum'] == 0 ? 0 : round($data[$v]['login_sum'] / count($login_data_distinct), 3);
                }
            }
        } else if (request()->get('type-date') == 2) { //按时间段显示
            $k = date('Y-m-d', $this->getTime()[0] / 1000) . ' 至 ' . date('Y-m-d', $this->getTime()[1] / 1000);

            $login_data_distinct = array_unique(array_column($login_data, 'userId'));
            $create_data_distinct = array_unique(array_column($create_data, 'userId'));

            $data[$k]['active'] = count(array_diff($login_data_distinct, $create_data_distinct));
            $data[$k]['login_sum'] = count($login_data);
            $data[$k]['login_avg'] = $data[$k]['login_sum'] == 0 ? 0 : round($data[$k]['login_sum'] / count($login_data_distinct), 3);
        }

        //把数据存储到文件中 方便导出和画图形
        $this->dataFile('save', 'data.txt', $data);

        return $this->standard_return_view('online.dau', $data);
    }

    //每日在线人数时段分布
    public function timedistribution()
    {
        $data_log = $this->getDoc('log_coll_game_real_data_log')->select(
            'onlineUsers',
            'logTime'
        )
            ->where('logTime', '>=', $this->getTime()[0])
            ->where('logTime', '<=', $this->getTime()[1]);

        if (request()->pid) {
            $data_log = $data_log->where('pid', request()->pid);
        }

        if (request()->serverId) {
            $data_log = $data_log->where('serverId', request()->serverId);
        }

        $data_log = $data_log->get()->toArray();

        $len = count($data_log);
        for ($i = 0; $i < $len; $i++) {
            $data_log[$i]['logTime'] = "'" . date('H:i', $data_log[$i]['logTime'] / 1000) . "'";
        }

        $data['x'] = implode(',', array_column($data_log, 'logTime'));
        $data['y'] = implode(',', array_column($data_log, 'onlineUsers'));

        return view('online.timedistribution', [
            'data' => $data,
            'start' => $this->getTime()[0],
            'end' => $this->getTime()[1],
            'pid' => $this->getPid(),
            'select_pid' => request()->get('pid') ? request()->get('pid') : 0,
            'server' => $this->getServer(),
            'serverId' => request()->get('serverId') ? request()->get('serverId') : 0,
        ]);
    }

    //在线时长统计
    public function lengthdistribution()
    {
        if (request()->status == 2) {//导出exl
            $this->exportExcel(['统计时间', '当天在线总时长', '平均在线时长', '单次在线时长', '在线[0-10min]时段人数', '在线[10-30min]时段人数', '在线[30-60min]时段人数', '在线[60min+]时段人数'], '平均在线时长区间分布');
            return;
        } elseif (in_array(request()->status, [3, 4, 5])) {//图像化显示
            $data = $this->dataFile('read', 'data.txt');

            $tmp_key = array_keys($data);
            $graph['x'] = '';
            foreach ($tmp_key as $v) {
                $graph['x'] .= "'" . $v . "'" . ',';
            }
            $x = rtrim($graph['x'], ',');

            switch (request()->status) {
                case 3:
                    $y = implode(',', array_column($data, 'duration'));
                    $y = "{'name':'总在线时长',data:[" . $y . "]}";

                    $graph = $this->discount_data('总在线时长', $x, $y, '时间', '时长');
                    break;
                case 4:
                    $avg_duration = implode(',', array_column($data, 'avg_duration'));
                    $avg_duration = "{'name':'平均在线时长',data:[" . $avg_duration . "]}";

                    $once_duration = implode(',', array_column($data, 'once_duration'));
                    $once_duration = "{'name':'单次在线时长',data:[" . $once_duration . "]}";

                    $y = $avg_duration . ',' . $once_duration;

                    $graph = $this->discount_data('平均在线时长、单次在线时长', $x, $y, '时间', '时长');
                    break;
                case 5:
                    $ten = implode(',', array_column($data, 'ten'));
                    $ten = "{'name':'[0-10min]时段人数',data:[" . $ten . "]}";

                    $thirty = implode(',', array_column($data, 'thirty'));
                    $thirty = "{'name':'[10-30min]时段人数',data:[" . $thirty . "]}";

                    $sixty = implode(',', array_column($data, 'sixty'));
                    $sixty = "{'name':'[30-60min]以上时段人数',data:[" . $sixty . "]}";

                    $moreTime = implode(',', array_column($data, 'moreTime'));
                    $moreTime = "{'name':'[60min]以上时段人数',data:[" . $moreTime . "]}";

                    $y = $ten . ',' . $thirty . ',' . $sixty . ',' . $moreTime;

                    $graph = $this->discount_data('各时段在线时长', $x, $y, '时间', '时长');
                    break;
            }

            return view('graph.line', ['data' => $graph, 'title' => '在线时长统计']);
        }

        //登录游戏的用户量
        $login_data = $this->getDoc('log_coll_login')
            ->select('logTime', 'userId')
            ->where('logTime', '>=', $this->getTime()[0])
            ->where('logTime', '<=', $this->getTime()[1]);

        if (request()->pid) {
            $login_data = $login_data->where('pid', request()->pid * 1);
        }
        if (request()->serverId) {
            $login_data = $login_data->where('serverId', request()->get('serverId') * 1);
        }

        $login_data = $login_data->get()->toArray();
        $login_userId = array_column($login_data, 'userId');

        //退出游戏 不加结束时间
        $loginoff_data = $this->getDoc('log_coll_logoff')
            ->select('logTime', 'userId')
            ->where('logTime', '>=', $this->getTime()[0])
            ->whereIn('userId', $login_userId);

        if (request()->pid) {
            $loginoff_data = $loginoff_data->where('pid', request()->pid * 1);
        }

        if (request()->serverId) {
            $loginoff_data = $loginoff_data->where('serverId', request()->get('serverId') * 1);
        }

        $loginoff_data = $loginoff_data->get()->toArray();

        $data = [];
        if (request()->get('type-date') == 1) { //按日显示
            $login_data = $this->rearrayToTime($login_data, 'logTime');//按时间分组

            $loginoff_userId = array_column($loginoff_data, 'userId');
            $loginoff_logTime = array_column($loginoff_data, 'logTime');

            foreach ($login_data as $k => $v) {
                //在线总时长 在线次数
                $duration = $time = $ten = $thirty = $sixty = $moreTime = $number = 0;

                foreach ($v as $value) {
                    $index = array_search($value['userId'], $loginoff_userId);

                    if ($index !== false) {
                        $onlint_time = abs($loginoff_logTime[$index] - $value['logTime']);
                        $duration += $onlint_time;
                        $time++;

                        if ($onlint_time > 0 && $onlint_time <= 600000) {
                            $ten++;
                        } elseif ($onlint_time > 600000 && $onlint_time <= 1800000) {
                            $thirty++;
                        } elseif ($onlint_time > 1800000 && $onlint_time <= 3600000) {
                            $sixty++;
                        } else {
                            $moreTime++;
                        }
                    }

                    unset($loginoff_userId[$index]);
                }

                $number = count(array_unique(array_column($v, 'userId'))); //在线人数

                $data[$k]['duration'] = $duration == 0 ? 0 : round($duration / 60000, 3);
                $data[$k]['avg_duration'] = $duration == 0 ? 0 : round($duration / $number / 60000, 3);
                $data[$k]['once_duration'] = $duration == 0 ? 0 : round($duration / $time / 60000, 3);
                $data[$k]['ten'] = $ten;
                $data[$k]['thirty'] = $thirty;
                $data[$k]['sixty'] = $sixty;
                $data[$k]['moreTime'] = $moreTime;
            }
            //后期处理 没有的补上
            $tmp = [
                'duration' => 0,
                'avg_duration' => 0,
                'once_duration' => 0,
                'ten' => 0,
                'thirty' => 0,
                'sixty' => 0,
                'moreTime' => 0
            ];
            $this->fill($data, $tmp);
        } else if (request()->get('type-date') == 2) { //按时间段显示

            $k = date('Y-m-d', $this->getTime()[0] / 1000) . ' 至 ' . date('Y-m-d', $this->getTime()[1] / 1000);

            $loginoff_userId = array_column($loginoff_data, 'userId');
            $loginoff_logTime = array_column($loginoff_data, 'logTime');

            //在线总时长 在线次数
            $duration = $time = $ten = $thirty = $sixty = $moreTime = $number = 0;
            foreach ($login_data as $value) {
                $index = array_search($value['userId'], $loginoff_userId);

                if ($index !== false) {
                    $onlint_time = abs($loginoff_logTime[$index] - $value['logTime']);
                    $duration += $onlint_time;
                    $time++;

                    if ($onlint_time > 0 && $onlint_time <= 600000) {
                        $ten++;
                    } elseif ($onlint_time > 600000 && $onlint_time <= 1800000) {
                        $thirty++;
                    } elseif ($onlint_time > 1800000 && $onlint_time <= 3600000) {
                        $sixty++;
                    } else {
                        $moreTime++;
                    }

                    unset($loginoff_userId[$index]);
                }
            }

            $number = count(array_unique(array_column($login_data)));

            $data[$k]['duration'] = $duration == 0 ? 0 : round($duration / 60000, 3);
            $data[$k]['avg_duration'] = $duration == 0 ? 0 : round($duration / $number / 60000, 3);
            $data[$k]['once_duration'] = $duration == 0 ? 0 : round($duration / $time / 60000, 3);
            $data[$k]['ten'] = $ten;
            $data[$k]['thirty'] = $thirty;
            $data[$k]['sixty'] = $sixty;
            $data[$k]['moreTime'] = $moreTime;
        }

        //把数据存储到文件中 方便导出和画图形
        $this->dataFile('save', 'data.txt', $data);

        return $this->standard_return_view('online.lengthdistribution', $data);
    }
}