<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class userController extends Controller
{
    public function total()
    {
        if (request()->status == 2) {//导出exl
            $this->exportExcel(['日期', '新增用户量', '活跃用户量', '登录次数', '人均登录次数', '人均登录次数增幅'], '用户总况');
            return;
        } elseif (in_array(request()->status, [3, 4, 5, 6])) {//图像化显示
            $data = $this->dataFile('read', 'data.txt');

            $tmp_key = array_keys($data);
            $graph['x'] = '';
            foreach ($tmp_key as $v) {
                $graph['x'] .= "'" . $v . "'" . ',';
            }
            $x = rtrim($graph['x'], ',');

            switch (request()->status) {
                case 3:
                    $y = implode(',', array_column($data, 'new_user'));
                    $y = "{'name':'新增用户量',data:[" . $y . "]}";

                    $graph = $this->discount_data('新增用户量', $x, $y, '时间', '人数');
                    break;
                case 4:
                    $y = implode(',', array_column($data, 'active'));
                    $y = "{'name':'活跃用户',data:[" . $y . "]}";

                    $graph = $this->discount_data('活跃用户', $x, $y, '时间', '人数');
                    break;
                case 5:
                    $y = implode(',', array_column($data, 'login_avg_sum'));
                    $y = "{'name':'人均登录次数',data:[" . $y . "]}";

                    $graph = $this->discount_data('人均登录次数', $x, $y, '时间', '人数');
                    break;
                case 6:
                    $y = implode(',', array_column($data, 'login_avg_rat'));
                    $y = "{'name':'人均登录次数幅度',data:[" . $y . "]}";

                    $graph = $this->discount_data('人均登录次数幅度', $x, $y, '时间', '人数');
                    break;
            }

            return view('graph.line', ['data' => $graph, 'title' => '用户总况']);
        }

        //查询新增用户数
        $db_head = $this->getDoc('log_coll_create_user')->select(
            'userId', 'logTime'
        )
            ->where('logTime', '>=', $this->getTime()[0])
            ->where('logTime', '<=', $this->getTime()[1]);
        if ((int)request()->pid) {
            $db_head = $db_head->where('pid', (int)request()->pid);
        }
        if (request()->serverId) {
            $db_head = $db_head->where('serverId', (int)request()->serverId);
        }

        $db_data_new_user = $db_head->get();

        //登录游戏的用户量
        $db_head = $this->getDoc('log_coll_login')
            ->select('userId', 'logTime')
            ->where('logTime', '>=', $this->getTime()[0])
            ->where('logTime', '<=', $this->getTime()[1]);
        if ((int)request()->pid) {
            $db_head = $db_head->where('pid', (int)request()->pid);
        }
        if (request()->serverId) {
            $db_head = $db_head->where('serverId', (int)request()->serverId);
        }
        $db_data_login = $db_head->get()->toArray();

        //下一天的登录量
        $db_head = $this->getDoc('log_coll_login')
            ->select('userId', 'logTime')
            ->where('logTime', '>=', $this->getTime()[0] - $this->day_time)
            ->where('logTime', '<=', $this->getTime()[1] - $this->day_time);
        if ((int)request()->pid) {
            $db_head = $db_head->where('pid', (int)request()->pid);
        }
        if (request()->serverId) {
            $db_head = $db_head->where('serverId', (int)request()->serverId);
        }
        $db_data_login_pre = $db_head->get()->toArray();

        //创建角色数量
        $db_head = $this->getDoc('log_coll_create_role')
            ->select('userId', 'logTime')
            ->where('logTime', '>=', $this->getTime()[0])
            ->where('logTime', '<=', $this->getTime()[1]);
        if ((int)request()->pid) {
            $db_head = $db_head->where('pid', (int)request()->pid);
        }
        if (request()->serverId) {
            $db_head = $db_head->where('serverId', (int)request()->serverId);
        }
        $db_data_create_role = $db_head->get()->toArray();

        $data = [];
        if (request()->get('type-date') == 1) { //按日显示
            //按时间分组
            $db_data_new_user = $this->rearrayToTime($db_data_new_user, 'logTime');
            $db_data_login = $this->rearrayToTime($db_data_login, 'logTime');
            $db_data_create_role = $this->rearrayToTime($db_data_create_role, 'logTime');
            $db_data_login_pre = $this->rearrayToTime($db_data_login_pre, 'logTime');

            //获取时间列表
            $this_date = $this->generationTimeSeries($this->getTime()[0], $this->getTime()[1]);

            foreach ($this_date as $k => $v) {
                $data[$v] = [];
                //新增用户量
                if (isset($db_data_new_user[$v]) && !empty($db_data_new_user[$v])) {
                    $data[$v]['new_user'] = count(array_unique(array_column($db_data_new_user[$v], 'userId')));
                } else {
                    $data[$v]['new_user'] = 0;
                }

                //活跃用户量
                if (isset($db_data_login[$v])) {
                    $db_data_login_tmp = array_unique(array_column($db_data_login[$v], 'userId'));//去重后的登录人数
                    $db_data_create_role_tmp = isset($db_data_create_role[$v]) ? array_unique(array_column($db_data_create_role[$v], 'userId')) : [];//去重后的重建角色人数

                    $data[$v]['active'] = count(array_diff($db_data_login_tmp, $db_data_create_role_tmp));

                    $data[$v]['login_sum'] = count($db_data_login[$v]);

                    $data[$v]['login_avg_sum'] = $data[$v]['login_sum'] == 0 ? 0 : $data[$v]['login_sum'] / count($db_data_login[$v]);

                    $pre_date = date('Y-m-d', strtotime($v) - $this->day_time / 1000);

                    if (isset($db_data_login_pre[$pre_date])) {
                        $data[$v]['login_avg_rat'] = round((count($db_data_login[$v]) - count($db_data_login_pre[$pre_date])) / count($db_data_login[$v]), 3);
                    } else {
                        $data[$v]['login_avg_rat'] = 1;
                    }
                } else {
                    $data[$v]['active'] = $data[$v]['login_sum'] = $data[$v]['login_avg_sum'] = $data[$v]['login_avg_rat'] = 0;
                }
            }

        } else if (request()->get('type-date') == 2) { //按时间段显示
            $k = date('Y-m-d', $this->getTime()[0] / 1000) . ' 至 ' . date('Y-m-d', $this->getTime()[1] / 1000);

            //新增用户量
            $data[$k]['new_user'] = count(array_unique(array_column($db_data_new_user, 'userId')));

            //活跃用户量
            $db_data_login = array_unique(array_column($db_data_login, 'userId'));//去重后的登录人数
            $db_data_create_role = array_unique(array_column($db_data_login, 'userId'));//去重后的重建角色人数
            $active = 0;
            foreach ($db_data_login as $v) {
                if (!in_array($v, $db_data_create_role)) {
                    $active++;
                }
            }
            $data[$k]['active'] = $active;
            //登录次数
            $data[$k]['login_sum'] = count($db_data_login);
            //人均登录次数
            $data[$k]['login_avg_sum'] = $data[$k]['login_sum'] == 0 ? 0 : $data[$k]['login_sum'] / count($db_data_login);
            //登录人数幅度
            $data[$k]['login_avg_rat'] = '---';
        }

        //把数据存储到文件中 方便导出和画图形
        $this->dataFile('save', 'data.txt', $data);

        return $this->standard_return_view('user.total', $data);
    }

    //实时用户总况
    public function realtimertotal()
    {
        if (request()->status == 2) {//导出exl
            $this->exportExcel(['日期', '新增用户量', '活跃用户量', '登录次数', '人均登录次数', '人均登录次数增幅'], '用户总况');
            return;
        } elseif (in_array(request()->status, [3, 4, 5, 6])) {//图像化显示
            $data = $this->dataFile('read', 'data.txt');

            $tmp_key = array_keys($data);
            $graph['x'] = '';
            foreach ($tmp_key as $v) {
                $graph['x'] .= "'" . $v . "'" . ',';
            }
            $x = rtrim($graph['x'], ',');

            switch (request()->status) {
                case 3:
                    $y = implode(',', array_column($data, 'new_user'));
                    $y = "{'name':'新增用户量',data:[" . $y . "]}";

                    $graph = $this->discount_data('新增用户量', $x, $y, '时间', '人数');
                    break;
                case 4:
                    $y = implode(',', array_column($data, 'active'));
                    $y = "{'name':'活跃用户',data:[" . $y . "]}";

                    $graph = $this->discount_data('活跃用户', $x, $y, '时间', '人数');
                    break;
                case 5:
                    $y = implode(',', array_column($data, 'login_avg_sum'));
                    $y = "{'name':'人均登录次数',data:[" . $y . "]}";

                    $graph = $this->discount_data('人均登录次数', $x, $y, '时间', '人数');
                    break;
                case 6:
                    $y = implode(',', array_column($data, 'login_avg_rat'));
                    $y = "{'name':'人均登录次数幅度',data:[" . $y . "]}";

                    $graph = $this->discount_data('人均登录次数幅度', $x, $y, '时间', '人数');
                    break;
            }

            return view('graph.line', ['data' => $graph, 'title' => '实时用户总况']);
        }

        //查询新增用户数
        $db_head = $this->getDoc('log_coll_create_user')->select(
            'userId', 'logTime'
        )
            ->where('logTime', '>=', $this->getTime()[0])
            ->where('logTime', '<=', $this->getTime()[1]);
        if ((int)request()->pid) {
            $db_head = $db_head->where('pid', (int)request()->pid);
        }

        $db_data_new_user = $db_head->get();

        //登录游戏的用户量
        $db_head = $this->getDoc('log_coll_login')
            ->select('userId', 'logTime')
            ->where('logTime', '>=', $this->getTime()[0])
            ->where('logTime', '<=', $this->getTime()[1]);
        if ((int)request()->pid) {
            $db_head = $db_head->where('pid', (int)request()->pid);
        }
        if (request()->serverId) {
            $db_head = $db_head->where('serverId', (int)request()->serverId);
        }
        $db_data_login = $db_head->get()->toArray();

        //上一小时的登录量
        $db_head = $this->getDoc('log_coll_login')
            ->select('userId', 'logTime')
            ->where('logTime', '>=', $this->getTime()[0] - 3600000)
            ->where('logTime', '<=', $this->getTime()[1] - 3600000);
        if ((int)request()->pid) {
            $db_head = $db_head->where('pid', (int)request()->pid);
        }
        if (request()->serverId) {
            $db_head = $db_head->where('serverId', (int)request()->serverId);
        }
        $db_data_login_pre = $db_head->get()->toArray();

        //创建角色数量
        $db_head = $this->getDoc('log_coll_create_role')
            ->select('userId', 'logTime')
            ->where('logTime', '>=', $this->getTime()[0])
            ->where('logTime', '<=', $this->getTime()[1]);
        if ((int)request()->pid) {
            $db_head = $db_head->where('pid', (int)request()->pid);
        }
        if (request()->serverId) {
            $db_head = $db_head->where('serverId', (int)request()->serverId);
        }
        $db_data_create_role = $db_head->get()->toArray();

        $data = [];

        //按时间分组
        $db_data_new_user = $this->rearrayToHour($db_data_new_user, 'logTime');
        $db_data_login = $this->rearrayToHour($db_data_login, 'logTime');
        $db_data_create_role = $this->rearrayToHour($db_data_create_role, 'logTime');
        $db_data_login_pre = $this->rearrayToHour($db_data_login_pre, 'logTime');

        //获取时间列表
        $this_date = $this->generationHourSeries($this->getTime()[0], $this->getTime()[1]);

        foreach ($this_date as $k => $v) {
            $data[$v] = [];
            //新增用户量
            if (isset($db_data_new_user[$v]) && !empty($db_data_new_user[$v])) {
                $data[$v]['new_user'] = count(array_unique(array_column($db_data_new_user[$v], 'userId')));
            } else {
                $data[$v]['new_user'] = 0;
            }

            //活跃用户量
            if (isset($db_data_login[$v])) {
                $db_data_login_tmp = array_unique(array_column($db_data_login[$v], 'userId'));//去重后的登录人数
                $db_data_create_role_tmp = isset($db_data_create_role[$v]) ? array_unique(array_column($db_data_create_role[$v], 'userId')) : [];//去重后的重建角色人数

                $data[$v]['active'] = count(array_diff($db_data_login_tmp, $db_data_create_role_tmp));

                $data[$v]['login_sum'] = count($db_data_login[$v]);

                $data[$v]['login_avg_sum'] = $data[$v]['login_sum'] == 0 ? 0 : $data[$v]['login_sum'] / count($db_data_login[$v]);

                $pre_date = date('Y-m-d', strtotime($v) - $this->day_time / 1000);
                if (isset($db_data_login_pre[$pre_date])) {
                    $data[$v]['login_avg_rat'] = round((count($db_data_login[$v]) - count($db_data_login_pre[$pre_date])) / count($db_data_login[$v]), 3);
                } else {
                    $data[$v]['login_avg_rat'] = 1;
                }
            } else {
                $data[$v]['active'] = $data[$v]['login_sum'] = $data[$v]['login_avg_sum'] = $data[$v]['login_avg_rat'] = 0;
            }
        }

        //把数据存储到文件中 方便导出和画图形
        $this->dataFile('save', 'data.txt', $data);

        return $this->standard_return_view('user.realtimertotal', $data);
    }

    //新增用户留存率
    public function newkeep()
    {
        if (request()->status == 2) {//导出exl
            $this->exportExcel(['日期', '次日留存', '三日留存', '周留存', '半月留存', '月留存'], '新增用户留存');
            return;
        } elseif (request()->status == 3) {//图像化显示
            $data = $this->dataFile('read', 'data.txt');

            $tmp_key = array_keys($data);
            $graph['x'] = '';
            foreach ($tmp_key as $v) {
                $graph['x'] .= "'" . $v . "'" . ',';
            }
            $x = rtrim($graph['x'], ',');

            $day = implode(',', array_column($data, 'day'));
            $day = "{'name':'次日留存',data:[" . $day . "]}";

            $threeday = implode(',', array_column($data, 'threeday'));
            $threeday = "{'name':'三日留存',data:[" . $threeday . "]}";

            $week = implode(',', array_column($data, 'week'));
            $week = "{'name':'周留存',data:[" . $week . "]}";

            $halfmonth = implode(',', array_column($data, 'halfmonth'));
            $halfmonth = "{'name':'半月留存',data:[" . $halfmonth . "]}";

            $month = implode(',', array_column($data, 'month'));
            $month = "{'name':'月留存',data:[" . $month . "]}";

            $y = $day . ',' . $threeday . ',' . $week . ',' . $halfmonth . ',' . $month;

            $graph = $this->discount_data(' 新增用户留存率', $x, $y, '时间', '人数');

            return view('graph.line', ['data' => $graph]);
        }

        //查询所有注册的用户 近期两个月采样
        $db_head = $this->getDoc('log_coll_create_user')
            ->select('userId', 'logTime')
            ->where('logTime', '>=', $this->getTime()[0] - $this->day_time * 60)
            ->where('logTime', '<=', $this->getTime()[1]);
        if ((int)request()->pid) {
            $db_head = $db_head->where('pid', (int)request()->pid);
        }

        $db_data_create_user = $db_head->get()->toArray();

        //查询所有登陆的用户 近期两个月采样
        $db_head = $this->getDoc('log_coll_login')
            ->select('userId', 'logTime')
            ->where('logTime', '>=', $this->getTime()[0] - $this->day_time * 60)
            ->where('logTime', '<=', $this->getTime()[1]);
        if ((int)request()->pid) {
            $db_head = $db_head->where('pid', (int)request()->pid);
        }
        if (request()->serverId) {
            $db_head = $db_head->where('serverId', (int)request()->serverId);
        }
        $db_data_login = $db_head->get()->toArray();

        //查询指定时间的注册量
        $db_head = $this->getDoc('log_coll_create_user')
            ->select('userId', 'logTime')
            ->where('logTime', '>=', $this->getTime()[0])
            ->where('logTime', '<=', $this->getTime()[1]);
        if (request()->pid) {
            $db_head = $db_head->where('pid', (int)request()->pid);
        }
        $create_user = $db_head->get()->toArray();

        $data = [];
        if (request()->get('type-date') == 1) { //按日显示

            $create_user = $this->rearrayToTime($create_user, 'logTime');

            //获取时间列表
            $this_date = $this->generationTimeSeries($this->getTime()[0] - $this->day_time, $this->getTime()[1]);

            $tmp['day'] = $tmp['threeday'] = $tmp['week'] = $tmp['halfmonth'] = $tmp['month'] = 0;

            $login_user_userid = array_column($db_data_login, 'userId');


            foreach ($db_data_create_user as $v) {
                $this_create_time = $v['logTime'];//用户注册时间

                //用户注册后第一次登陆的时间
                $index = array_search($v['userId'], $login_user_userid);
                if ($index) {
                    $this_login_time = $db_data_login[$index]['logTime'];

                    $interval = ceil(($this_login_time - $this_create_time) / $this->day_time);
                    switch ($interval) {
                        case 1:
                            $tmp['day']++;
                            break;
                        case 3:
                            $tmp['threeday']++;
                            break;
                        case 7:
                            $tmp['week']++;
                            break;
                        case 15:
                            $tmp['halfmonth']++;
                            break;
                        case 30:
                            $tmp['month']++;
                    }
                }
            }

            foreach ($this_date as $v) {
                $data[$v]['day'] = $data[$v]['threeday'] = $data[$v]['week'] = $data[$v]['halfmonth'] = $data[$v]['month'] = 0;

                if (isset($create_user[$v])) {
                    $today_creat = count($create_user[$v]);//今天的注册用户量

                    $data[$v]['day'] = round($tmp['day'] / $today_creat, 3);
                    $data[$v]['threeday'] = round($tmp['threeday'] / $today_creat, 3);
                    $data[$v]['week'] = round($tmp['week'] / $today_creat, 3);
                    $data[$v]['halfmonth'] = round($tmp['halfmonth'] / $today_creat, 3);
                    $data[$v]['month'] = round($tmp['month'] / $today_creat, 3);
                }
            }
        } else if (request()->get('type-date') == 2) { //按时间段显示

            $k = date('Y-m-d', $this->getTime()[0] / 1000) . ' 至 ' . date('Y-m-d', $this->getTime()[1] / 1000);

            $login_user = array_column($db_data_login, 'userId');

            $tmp['day'] = $tmp['threeday'] = $tmp['week'] = $tmp['halfmonth'] = $tmp['month'] = 0;

            foreach ($db_data_create_user as $v) {

                $this_create_time = $v['logTime'];//当前新增用户的添加时间

                $index = array_search($v['userId'], $login_user);

                if ($index) {
                    $this_login_time = $login_user[$index]['logTime'];
                }

                $interval = ceil(($this_login_time - $this_create_time) / $this->day_time);

                switch ($interval) {
                    case 1:
                        $tmp['day']++;
                        break;
                    case 3:
                        $tmp['threeday']++;
                        break;
                    case 7:
                        $tmp['week']++;
                        break;
                    case 15:
                        $tmp['halfmonth']++;
                        break;
                    case 30:
                        $tmp['month']++;
                }
            }

            $data[$k]['day'] = $data[$k]['threeday'] = $data[$k]['week'] = $data[$k]['halfmonth'] = $data[$k]['month'] = 0;

            $today_creat = count($create_user);
            if ($today_creat) {
                $data[$k]['day'] = round($tmp['day'] / $today_creat, 3);
                $data[$k]['threeday'] = round($tmp['threeday'] / $today_creat, 3);
                $data[$k]['week'] = round($tmp['week'] / $today_creat, 3);
                $data[$k]['halfmonth'] = round($tmp['halfmonth'] / $today_creat, 3);
                $data[$k]['month'] = round($tmp['month'] / $today_creat, 3);
            }
        } else if (request()->get('type-date') == 3) { //按时间段显示
            $create_user = $this->rearrayToTime($create_user, 'logTime', 'H');

            //获取时间列表
            $this_date = $this->generationHourSeries($this->getTime()[0], $this->getTime()[1], 'H');

            $tmp['day'] = $tmp['threeday'] = $tmp['week'] = $tmp['halfmonth'] = $tmp['month'] = 0;

            $login_user_userid = array_column($db_data_login, 'userId');

            foreach ($db_data_create_user as $v) {

                $this_create_time = $v['logTime'];//当前新增用户的添加时间

                $index = array_search($v['userId'], $login_user_userid);

                if ($index) {
                    $this_login_time = $db_data_login[$index]['logTime'];
                }

                $interval = ceil(($this_login_time - $this_create_time) / $this->day_time);

                switch ($interval) {
                    case 1:
                        $tmp['day']++;
                        break;
                    case 3:
                        $tmp['threeday']++;
                        break;
                    case 7:
                        $tmp['week']++;
                        break;
                    case 15:
                        $tmp['halfmonth']++;
                        break;
                    case 30:
                        $tmp['month']++;
                }
            }

            foreach ($this_date as $v) {
                $data[$v]['day'] = $data[$v]['threeday'] = $data[$v]['week'] = $data[$v]['halfmonth'] = $data[$v]['month'] = 0;

                if (isset($create_user[$v])) {
                    $today_creat = count($create_user[$v]);
                    $data[$v]['day'] = round($tmp['day'] / $today_creat, 3);
                    $data[$v]['threeday'] = round($tmp['threeday'] / $today_creat, 3);
                    $data[$v]['week'] = round($tmp['week'] / $today_creat, 3);
                    $data[$v]['halfmonth'] = round($tmp['halfmonth'] / $today_creat, 3);
                    $data[$v]['month'] = round($tmp['month'] / $today_creat, 3);
                }
            }
        }

        //把数据存储到文件中 方便导出和画图形
        $this->dataFile('save', 'data.txt', $data);
        return $this->standard_return_view('user.newkeep', $data);
    }

    //活跃用户留存率
    public function activekeep()
    {
        if (request()->status == 2) {//导出exl
            $this->exportExcel(['日期', '次日留存', '三日留存', '周留存', '半月留存', '月留存'], '活跃用户留存');
            return;
        } elseif (request()->status == 3) {//图像化显示
            $data = $this->dataFile('read', 'data.txt');

            $tmp_key = array_keys($data);
            $graph['x'] = '';
            foreach ($tmp_key as $v) {
                $graph['x'] .= "'" . $v . "'" . ',';
            }
            $x = rtrim($graph['x'], ',');

            $day = implode(',', array_column($data, 'day'));
            $day = "{'name':'次日留存',data:[" . $day . "]}";

            $threeday = implode(',', array_column($data, 'threeday'));
            $threeday = "{'name':'三日留存',data:[" . $threeday . "]}";

            $week = implode(',', array_column($data, 'week'));
            $week = "{'name':'周留存',data:[" . $week . "]}";

            $halfmonth = implode(',', array_column($data, 'halfmonth'));
            $halfmonth = "{'name':'半月留存',data:[" . $halfmonth . "]}";

            $month = implode(',', array_column($data, 'month'));
            $month = "{'name':'月留存',data:[" . $month . "]}";

            $y = $day . ',' . $threeday . ',' . $week . ',' . $halfmonth . ',' . $month;

            $graph = $this->discount_data(' 活跃用户留存', $x, $y, '时间', '人数');

            return view('graph.line', ['data' => $graph]);
        }

        //查询所有注册的用户
        $db_head = $this->getDoc('log_coll_create_user')
            ->select('userId', 'logTime')
            ->where('logTime', '>=', $this->getTime()[0] - $this->day_time * 60)
            ->where('logTime', '<=', $this->getTime()[1]);
        if ((int)request()->pid) {
            $db_head = $db_head->where('pid', (int)request()->pid);
        }
        $db_data_create_user = $db_head->get()->toArray();

        //查询所有登陆的用户
        $db_head = $this->getDoc('log_coll_login')
            ->select('userId', 'logTime')
            ->where('logTime', '>=', $this->getTime()[0] - $this->day_time * 60)
            ->where('logTime', '<=', $this->getTime()[1]);
        if ((int)request()->pid) {
            $db_head = $db_head->where('pid', (int)request()->pid);
        }
        if (request()->serverId) {
            $db_head = $db_head->where('serverId', (int)request()->serverId);
        }
        $db_data_login = $db_head->get()->toArray();

        //查询指定时间的注册量
        $db_head = $this->getDoc('log_coll_create_user')
            ->select('userId', 'logTime')
            ->where('logTime', '>=', $this->getTime()[0])
            ->where('logTime', '<=', $this->getTime()[1]);
        if ((int)request()->pid) {
            $db_head = $db_head->where('pid', (int)request()->pid);
        }
        $create_user = $db_head->get()->toArray();

        //创建角色数量
        $db_head = $this->getDoc('log_coll_create_role')
            ->select('userId', 'logTime')
            ->where('logTime', '>=', $this->getTime()[0])
            ->where('logTime', '<=', $this->getTime()[1]);
        if ((int)request()->pid) {
            $db_head = $db_head->where('pid', (int)request()->pid);
        }
        if (request()->serverId) {
            $db_head = $db_head->where('serverId', (int)request()->serverId);
        }
        $db_data_create_role = $db_head->get()->toArray();

        $data = [];
        if (request()->get('type-date') == 1) { //按日显示

            $create_user = $this->rearrayToTime($create_user, 'logTime');
            $db_data_login_dis = $this->rearrayToTime($db_data_login, 'logTime');
            $db_data_create_role = $this->rearrayToTime($db_data_create_role, 'logTime');

            //获取时间列表
            $this_date = $this->generationTimeSeries($this->getTime()[0] - $this->day_time, $this->getTime()[1]);

            $tmp['day'] = $tmp['threeday'] = $tmp['week'] = $tmp['halfmonth'] = $tmp['month'] = 0;

            $login_user_userid = array_column($db_data_login, 'userId');

            foreach ($db_data_create_user as $v) {

                $this_create_time = $v['logTime'];//当前新增用户的添加时间

                $index = array_search($v['userId'], $login_user_userid);

                if ($index) {
                    $this_login_time = $db_data_login[$index]['logTime'];
                }

                $interval = ceil(($this_login_time - $this_create_time) / $this->day_time);

                switch ($interval) {
                    case 1:
                        $tmp['day']++;
                        break;
                    case 3:
                        $tmp['threeday']++;
                        break;
                    case 7:
                        $tmp['week']++;
                        break;
                    case 15:
                        $tmp['halfmonth']++;
                        break;
                    case 30:
                        $tmp['month']++;
                }
            }

            foreach ($this_date as $v) {
                $data[$v]['day'] = $data[$v]['threeday'] = $data[$v]['week'] = $data[$v]['halfmonth'] = $data[$v]['month'] = 0;

                $db_data_login_dis_w = isset($db_data_login_dis[$v]) ? array_unique(array_column($db_data_login_dis[$v], 'userId')) : [];//去重后的登录用户
                $db_data_create_role_dis = isset($db_data_create_role[$v]) ? array_unique(array_column($db_data_create_role[$v], 'userId')) : [];//去重后的重建角色人数

                if (isset($create_user[$v])) {
                    $today_creat = count(array_diff($db_data_login_dis_w, $db_data_create_role_dis));//当日的活跃用户量

                    $data[$v]['day'] = round($tmp['day'] / $today_creat, 3);
                    $data[$v]['threeday'] = round($tmp['threeday'] / $today_creat, 3);
                    $data[$v]['week'] = round($tmp['week'] / $today_creat, 3);
                    $data[$v]['halfmonth'] = round($tmp['halfmonth'] / $today_creat, 3);
                    $data[$v]['month'] = round($tmp['month'] / $today_creat, 3);
                }
            }
        } else if (request()->get('type-date') == 2) { //按时间段显示

            $db_data_login_dis_w = isset($db_data_login_dis) ? array_unique(array_column($db_data_login_dis, 'userId')) : [];//去重后的登录用户
            $db_data_create_role_dis = isset($db_data_create_role) ? array_unique(array_column($db_data_create_role, 'userId')) : [];//去重后的重建角色人数

            $k = date('Y-m-d', $this->getTime()[0] / 1000) . ' 至 ' . date('Y-m-d', $this->getTime()[1] / 1000);

            $login_user = array_column($db_data_login, 'userId');

            $tmp['day'] = $tmp['threeday'] = $tmp['week'] = $tmp['halfmonth'] = $tmp['month'] = 0;

            foreach ($db_data_create_user as $v) {

                $this_create_time = $v['logTime'];//当前新增用户的添加时间

                $index = array_search($v['userId'], $login_user);

                if ($index) {
                    $this_login_time = $login_user[$index]['logTime'];
                }

                $interval = ceil(($this_login_time - $this_create_time) / $this->day_time);

                switch ($interval) {
                    case 1:
                        $tmp['day']++;
                        break;
                    case 3:
                        $tmp['threeday']++;
                        break;
                    case 7:
                        $tmp['week']++;
                        break;
                    case 15:
                        $tmp['halfmonth']++;
                        break;
                    case 30:
                        $tmp['month']++;
                }
            }

            $data[$k]['day'] = $data[$k]['threeday'] = $data[$k]['week'] = $data[$k]['halfmonth'] = $data[$k]['month'] = 0;

            $today_creat = count(array_diff($db_data_login_dis_w, $db_data_create_role_dis));//当日的活跃用户量
            if ($today_creat) {
                $data[$k]['day'] = round($tmp['day'] / $today_creat, 3);
                $data[$k]['threeday'] = round($tmp['threeday'] / $today_creat, 3);
                $data[$k]['week'] = round($tmp['week'] / $today_creat, 3);
                $data[$k]['halfmonth'] = round($tmp['halfmonth'] / $today_creat, 3);
                $data[$k]['month'] = round($tmp['month'] / $today_creat, 3);
            }
        } else if (request()->get('type-date') == 3) { //按时间段显示
            $create_user = $this->rearrayToTime($create_user, 'logTime', 'H');
            $db_data_login_dis = $this->rearrayToTime($db_data_login, 'logTime', 'H');
            $db_data_create_role = $this->rearrayToTime($db_data_create_role, 'logTime', 'H');

            //获取时间列表
            $this_date = $this->generationHourSeries($this->getTime()[0], $this->getTime()[1], 'H');

            $tmp['day'] = $tmp['threeday'] = $tmp['week'] = $tmp['halfmonth'] = $tmp['month'] = 0;

            $login_user_userid = array_column($db_data_login, 'userId');

            foreach ($db_data_create_user as $v) {

                $this_create_time = $v['logTime'];//当前新增用户的添加时间

                $index = array_search($v['userId'], $login_user_userid);

                if ($index) {
                    $this_login_time = $db_data_login[$index]['logTime'];
                }

                $interval = ceil(($this_login_time - $this_create_time) / $this->day_time);

                switch ($interval) {
                    case 1:
                        $tmp['day']++;
                        break;
                    case 3:
                        $tmp['threeday']++;
                        break;
                    case 7:
                        $tmp['week']++;
                        break;
                    case 15:
                        $tmp['halfmonth']++;
                        break;
                    case 30:
                        $tmp['month']++;
                }
            }

            foreach ($this_date as $v) {
                $data[$v]['day'] = $data[$v]['threeday'] = $data[$v]['week'] = $data[$v]['halfmonth'] = $data[$v]['month'] = 0;

                $db_data_login_dis_w = isset($db_data_login_dis[$v]) ? array_unique(array_column($db_data_login_dis[$v], 'userId')) : [];//去重后的登录用户
                $db_data_create_role_dis = isset($db_data_create_role[$v]) ? array_unique(array_column($db_data_create_role[$v], 'userId')) : [];//去重后的重建角色人数

                if (isset($create_user[$v])) {
                    $today_creat = count(array_diff($db_data_login_dis_w, $db_data_create_role_dis));//当日的活跃用户量

                    $data[$v]['day'] = round($tmp['day'] / $today_creat, 3);
                    $data[$v]['threeday'] = round($tmp['threeday'] / $today_creat, 3);
                    $data[$v]['week'] = round($tmp['week'] / $today_creat, 3);
                    $data[$v]['halfmonth'] = round($tmp['halfmonth'] / $today_creat, 3);
                    $data[$v]['month'] = round($tmp['month'] / $today_creat, 3);
                }
            }
        }

        //把数据存储到文件中 方便导出和画图形
        $this->dataFile('save', 'data.txt', $data);
        return $this->standard_return_view('user.activekeep', $data);
    }

    //活跃用户
    public function active()
    {
        if (request()->status == 2) {//导出exl
            $this->exportExcel(['日期', '活跃用户', '活跃用户增幅'], '活跃用户');
            return;
        } elseif (in_array(request()->status, [3, 4])) {//图像化显示
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

                    $graph = $this->discount_data(' 活跃用户人数', $x, $y, '时间', '人数');
                    break;
                case 4:
                    $y = implode(',', array_column($data, 'active_rat'));
                    $y = "{'name':'活跃用户增幅',data:[" . $y . "]}";

                    $graph = $this->discount_data(' 活跃用户增幅', $x, $y, '时间', '人数');
                    break;
            }

            return view('graph.line', ['data' => $graph, 'title' => '活跃用户']);
        }
        /*
         * 登录游戏的用户量
         * 超前查询一天算增幅
         * */
        $db_head = $this->getDoc('log_coll_login')
            ->select('userId', 'logTime')
            ->where('logTime', '>=', $this->getTime()[0] - $this->day_time)
            ->where('logTime', '<=', $this->getTime()[1]);
        if ((int)request()->pid) {
            $db_head = $db_head->where('pid', (int)request()->pid);
        }
        if (request()->serverId) {
            $db_head = $db_head->where('serverId', (int)request()->serverId);
        }
        $db_data_login = $db_head->get()->toArray();

        //创建角色数量
        $db_head = $this->getDoc('log_coll_create_role')
            ->select('userId', 'logTime')
            ->where('logTime', '>=', $this->getTime()[0])
            ->where('logTime', '<=', $this->getTime()[1]);
        if ((int)request()->pid) {
            $db_head = $db_head->where('pid', (int)request()->pid);
        }
        if (request()->serverId) {
            $db_head = $db_head->where('serverId', (int)request()->serverId);
        }
        $db_data_create_role = $db_head->get()->toArray();

        $data = [];
        if (request()->get('type-date') == 1) { //按日显示
            //按时间分组
            $db_data_login = $this->rearrayToTime($db_data_login, 'logTime');
            $db_data_create_role = $this->rearrayToTime($db_data_create_role, 'logTime');

            //获取时间列表
            $this_date = $this->generationTimeSeries($this->getTime()[0] - $this->day_time, $this->getTime()[1]);

            $array_tmp = [];
            foreach ($this_date as $k => $v) {
                $data[$v] = [];

                $db_data_login_dis = isset($db_data_login[$v]) ? array_unique(array_column($db_data_login[$v], 'userId')) : [];//去重后的登录用户
                $db_data_create_role_dis = isset($db_data_create_role[$v]) ? array_unique(array_column($db_data_create_role[$v], 'userId')) : [];//去重后的重建角色人数

                //活跃用户量
                $data[$v]['active'] = count(array_diff($db_data_login_dis, $db_data_create_role_dis));

                $array_tmp[$k] = $data[$v]['active'];

                if ($k == 0) {
                    continue;
                }

                if ($array_tmp[$k] != 0) {
                    $data[$v]['active_rat'] = round(($array_tmp[$k] - $array_tmp[$k - 1]) / $array_tmp[$k], 3);
                } else {
                    $data[$v]['active_rat'] = 0;
                }
            }
            array_shift($data);
        } else if (request()->get('type-date') == 2) { //按时间段显示
            $k = date('Y-m-d', $this->getTime()[0] / 1000) . ' 至 ' . date('Y-m-d', $this->getTime()[1] / 1000);

            //活跃用户量
            $db_data_login = array_unique(array_column($db_data_login, 'userId'));//去重后的登录人数
            $db_data_create_role = array_unique(array_column($db_data_login, 'userId'));//去重后的重建角色人数

            $data[$k]['active'] = count(array_diff($db_data_login, $db_data_create_role));

            //登录人数幅度
            $data[$k]['active_rat'] = '---';
        }

        //把数据存储到文件中 方便导出和画图形
        $this->dataFile('save', 'data.txt', $data);

        return $this->standard_return_view('user.active', $data);
    }

    //流失用户
    public function chum()
    {
        //3月采样
        $db_head = $this->getDoc('log_coll_login')
            ->select('userId', 'logTime')
            ->where('logTime', '>=', $this->getTime()[1] - $this->day_time * 90)
            ->where('logTime', '<=', $this->getTime()[1]);
        if ((int)request()->pid) {
            $db_head = $db_head->where('pid', (int)request()->pid);
        }
        if (request()->serverId) {
            $db_head = $db_head->where('serverId', (int)request()->serverId);
        }
        $db_data_login = $db_head->orderBy('logTime', 'desc')->get()->toArray();
        $db_data_login = $this->array_unset_tt($db_data_login, 'userId');

        $week = $doubweek = $month = 0;
        foreach ($db_data_login as $v) {
            if ($v['logTime'] <= time() * 1000 - $this->day_time * 7) {
                $week++;
            }
            if ($v['logTime'] <= time() * 1000 - $this->day_time * 14) {
                $doubweek++;
            }
            if ($v['logTime'] <= time() * 1000 - $this->day_time * 30) {
                $month++;
            }
        }

        $data['time'] = date('Y-m-d', $this->getTime()[1] / 1000);
        $data['week'] = $week;
        $data['doubweek'] = $doubweek;
        $data['month'] = $month;

        return $this->standard_return_view('user.chum', $data);
    }

    //回流
    public function backflow()
    {
        //3月采样
        $db_head = $this->getDoc('log_coll_login')
            ->select('userId', 'logTime')
            ->where('logTime', '>=', $this->getTime()[1] - $this->day_time * 90)
            ->where('logTime', '<=', $this->getTime()[1] - $this->day_time);
        if ((int)request()->pid) {
            $db_head = $db_head->where('pid', (int)request()->pid);
        }
        if (request()->serverId) {
            $db_head = $db_head->where('serverId', (int)request()->serverId);
        }
        $db_data_login = $db_head->orderBy('logTime', 'desc')->get()->toArray();
        $db_data_login = $this->array_unset_tt($db_data_login, 'userId');

        $week = $doubweek = $month = [];
        foreach ($db_data_login as $v) {
            if ($v['logTime'] <= time() * 1000 - $this->day_time * 7) {
                $week[] = $v['userId'];
            }

            if ($v['logTime'] >= time() * 1000 - $this->day_time * 14) {
                $doubweek[] = $v['userId'];
            }

            if ($v['logTime'] >= time() * 1000 - $this->day_time * 30) {
                $month[] = $v['userId'];
            }
        }

        //查询今天登陆的用户
        $db_head = $this->getDoc('log_coll_login')
            ->distinct('userId')
            ->where('logTime', '>=', $this->getTime()[1] - $this->day_time)
            ->where('logTime', '<=', $this->getTime()[1]);
        if ((int)request()->pid) {
            $db_head = $db_head->where('pid', (int)request()->pid);
        }
        if (request()->serverId) {
            $db_head = $db_head->where('serverId', (int)request()->serverId);
        }
        $db_data_login_day = $db_head->get()->toArray();

        $data['time'] = date('Y-m-d', $this->getTime()[1] / 1000);
        $data['week'] = count(array_intersect($db_data_login_day, $week));
        $data['doubweek'] = count(array_intersect($db_data_login_day, $doubweek));
        $data['month'] = count(array_intersect($db_data_login_day, $month));

        return $this->standard_return_view('user.backflow', $data);
    }

    //充值用户
    public function rechargeuser()
    {
        if (request()->status == 2) {//导出exl
            $this->exportExcel(['日期', '新增充值用户数', '活跃充值用户数', '登陆次数', '人均登录次数'], '充值用户');
            return;
        } elseif (request()->status == 3) {//图像化显示
            $data = $this->dataFile('read', 'data.txt');

            $tmp_key = array_keys($data);
            $graph['x'] = '';
            foreach ($tmp_key as $v) {
                $graph['x'] .= "'" . $v . "'" . ',';
            }
            $x = rtrim($graph['x'], ',');

            $pay_user = implode(',', array_column($data, 'pay_user'));
            $pay_user = "{'name':'新增充值用户数',data:[" . $pay_user . "]}";

            $y = $pay_user;

            $graph = $this->discount_data(' 充值用户', $x, $y, '时间', '人数');

            return view('graph.line', ['data' => $graph]);
        } elseif (request()->status == 4) {//图像化显示
            $data = $this->dataFile('read', 'data.txt');

            $tmp_key = array_keys($data);
            $graph['x'] = '';
            foreach ($tmp_key as $v) {
                $graph['x'] .= "'" . $v . "'" . ',';
            }
            $x = rtrim($graph['x'], ',');

            $active_rat = implode(',', array_column($data, 'active_rat'));
            $active_rat = "{'name':'活跃用户',data:[" . $active_rat . "]}";

            $y = $active_rat;

            $graph = $this->discount_data(' 活跃用户增幅', $x, $y, '时间', '人数');

            return view('graph.line', ['data' => $graph]);
        }

        //查询新增充值用户
        $db_head = $this->getDoc('log_coll_recharge')
            ->select('userId', 'payTime')
            ->where('logTime', '>=', $this->getTime()[0])
            ->where('logTime', '<=', $this->getTime()[1]);
        if ((int)request()->pid) {
            $db_head = $db_head->where('pid', (int)request()->pid);
        }
        if (request()->serverId) {
            $db_head = $db_head->where('serverId', (int)request()->serverId);
        }
        $db_data_pay_user = $db_head->get()->toArray();

        //获取付费用户ID
        $pay_user_id = array_unique(array_column($db_data_pay_user, 'userId'));

        //查询创建角色用户量
        $db_head = $this->getDoc('log_coll_create_role')
            ->select('userId', 'logTime')
            ->whereIn('userId', $pay_user_id)
            ->where('logTime', '>=', $this->getTime()[0])
            ->where('logTime', '<=', $this->getTime()[1]);
        if ((int)request()->pid) {
            $db_head = $db_head->where('pid', (int)request()->pid);
        }
        if (request()->serverId) {
            $db_head = $db_head->where('serverId', (int)request()->serverId);
        }
        $db_data_create_role = $db_head->get()->toArray();

        //查询登录用户
        $db_head = $this->getDoc('log_coll_login')
            ->select('userId', 'logTime')
            ->whereIn('userId', $pay_user_id)
            ->where('logTime', '>=', $this->getTime()[0])
            ->where('logTime', '<=', $this->getTime()[1]);
        if ((int)request()->pid) {
            $db_head = $db_head->where('pid', (int)request()->pid);
        }
        if (request()->serverId) {
            $db_head = $db_head->where('serverId', (int)request()->serverId);
        }
        $db_data_login = $db_head->get()->toArray();

        $data = [];
        if (request()->get('type-date') == 1) { //按日显示
            $db_data_pay_user = $this->rearrayToTime($db_data_pay_user, 'payTime');
            $db_data_create_role = $this->rearrayToTime($db_data_create_role, 'logTime');
            $db_data_login = $this->rearrayToTime($db_data_login, 'logTime');

            //获取时间列表
            $this_date = $this->generationTimeSeries($this->getTime()[0], $this->getTime()[1]);

            foreach ($this_date as $v) {
                $tmp = [];
                if (isset($db_data_pay_user[$v])) {
                    $tmp = array_unique(array_column($db_data_pay_user[$v], 'userId'));
                    $data[$v]['pay_user'] = count($tmp);
                } else {
                    $data[$v]['pay_user'] = 0;
                }

                if (isset($db_data_create_role[$v])) {
                    $db_data_create_role_unique = array_unique(array_column($db_data_create_role[$v], 'userId'));

                    $data[$v]['active_pay_user'] = count(array_diff($tmp, $db_data_create_role_unique));
                } else {
                    $data[$v]['active_pay_user'] = 0;
                }

                if (isset($db_data_login[$v])) {
                    $data[$v]['login_sum'] = count(array_column($db_data_login[$v], 'userId'));
                    $data[$v]['login_sum_aver'] = $data[$v]['login_sum'] == 0 ? 0 : round($data[$v]['login_sum'] / count(array_unique(array_column($db_data_login[$v], 'userId'))), 3);
                } else {
                    $data[$v]['login_sum'] = $data[$v]['login_sum_aver'] = 0;
                }
            }
        } elseif (request()->get('type-date') == 2) { //按段显示
            $k = date('Y-m-d', $this->getTime()[0] / 1000) . ' 至 ' . date('Y-m-d', $this->getTime()[1] / 1000);

            $tmp = array_unique(array_column($db_data_pay_user, 'userId'));
            $data[$k]['pay_user'] = count($tmp);

            $db_data_create_role = array_unique(array_column($db_data_create_role, 'userId'));
            $data[$k]['active_pay_user'] = count(array_diff($tmp, $db_data_create_role));

            $data[$k]['login_sum'] = count(array_column($db_data_login, 'userId'));
            $data[$k]['login_sum_aver'] = $data[$k]['login_sum'] == 0 ? 0 : $data[$k]['login_sum'] / count(array_unique(array_column($db_data_login, 'userId')));
        } elseif (request()->get('type-date') == 3) {//按小时显示
            $db_data_pay_user = $this->rearrayToTime($db_data_pay_user, 'payTime', 'H');
            $db_data_create_role = $this->rearrayToTime($db_data_create_role, 'logTime', 'H');
            $db_data_login = $this->rearrayToTime($db_data_login, 'logTime', 'H');

            //获取时间列表
            $this_date = $this->generationHourSeries($this->getTime()[0], $this->getTime()[1], 'H');

            $tmp = [];
            foreach ($this_date as $v) {
                if (isset($db_data_pay_user[$v])) {
                    $tmp = array_unique(array_column($db_data_pay_user[$v], 'userId'));
                    $data[$v]['pay_user'] = count($tmp);
                } else {
                    $data[$v]['pay_user'] = 0;
                }

                if (isset($db_data_create_role[$v])) {
                    $db_data_create_role[$v] = array_unique(array_column($db_data_create_role[$v], 'userId'));

                    $data[$v]['active_pay_user'] = count(array_diff($tmp, $db_data_create_role[$v]));
                } else {
                    $data[$v]['active_pay_user'] = 0;
                }

                if (isset($db_data_login[$v])) {
                    $data[$v]['login_sum'] = count(array_column($db_data_login[$v], 'userId'));
                    $data[$v]['login_sum_aver'] = $data[$v]['login_sum'] == 0 ? 0 : round($data[$v]['login_sum'] / count(array_unique(array_column($db_data_login[$v], 'userId'))), 3);
                } else {
                    $data[$v]['login_sum'] = $data[$v]['login_sum_aver'] = 0;
                }
            }
        }
        //把数据存储到文件中 方便导出和画图形
        $this->dataFile('save', 'data.txt', $data);

        return $this->standard_return_view('user.rechargeuser', $data);
    }

    //充值额度
    public function rechargelimit()
    {
        if (request()->status == 2) {//导出exl
            $this->exportExcel(['日期', '总消费量', '人均消费量', '[0-1]消费人数', '[1-5]消费人数', '[5-10]消费人数', '[10-20]消费人数', '[20+]消费人数'], '充值额度');
            return;
        } elseif (request()->status == 3) {//图像化显示
            $data = $this->dataFile('read', 'data.txt');

            $tmp_key = array_keys($data);
            $graph['x'] = '';
            foreach ($tmp_key as $v) {
                $graph['x'] .= "'" . $v . "'" . ',';
            }
            $x = rtrim($graph['x'], ',');

            $total_recharge = implode(',', array_column($data, 'total_recharge'));
            $total_recharge = "{'name':'总消费量',data:[" . $total_recharge . "]}";

            $aver_recharge = implode(',', array_column($data, 'aver_recharge'));
            $aver_recharge = "{'name':'人均消费量',data:[" . $aver_recharge . "]}";

            $y = $total_recharge . ',' . $aver_recharge;

            $graph = $this->discount_data(' 消费额度', $x, $y, '时间', '元');

            return view('graph.line', ['data' => $graph]);
        } elseif (request()->status == 4) {//图像化显示
            $data = $this->dataFile('read', 'data.txt');

            $tmp_key = array_keys($data);
            $graph['x'] = '';
            foreach ($tmp_key as $v) {
                $graph['x'] .= "'" . $v . "'" . ',';
            }
            $x = rtrim($graph['x'], ',');

            $active_rat = implode(',', array_column($data, 'active_rat'));
            $active_rat = "{'name':'活跃用户',data:[" . $active_rat . "]}";

            $y = $active_rat;

            $graph = $this->discount_data(' 活跃用户增幅', $x, $y, '时间', '人数');

            return view('graph.line', ['data' => $graph]);
        }

        //查询今日新增充值用户
        $db_head = $this->getDoc('log_coll_recharge')
            ->select('userId', 'payTime', 'rechargeRMB')
            ->where('logTime', '>=', $this->getTime()[0])
            ->where('logTime', '<=', $this->getTime()[1]);
        if ((int)request()->pid) {
            $db_head = $db_head->where('pid', (int)request()->pid);
        }
        if (request()->serverId) {
            $db_head = $db_head->where('serverId', (int)request()->serverId);
        }
        $db_data_pay_user = $db_head->get()->toArray();


        $data = [];
        if (request()->get('type-date') == 1) { //按日显示
            $db_data_pay_user = $this->rearrayToTime($db_data_pay_user, 'payTime');

            //获取时间列表
            $this_date = $this->generationTimeSeries($this->getTime()[0], $this->getTime()[1]);

            $tmp = [];
            foreach ($this_date as $v) {

                $data[$v]['total_recharge'] = $data[$v]['aver_recharge'] = $data[$v]['1y'] = $data[$v]['5y'] = $data[$v]['10y'] = $data[$v]['20y'] = $data[$v]['more'] = 0;

                if (isset($db_data_pay_user[$v])) {

                    $recharge_arr = array_column($db_data_pay_user[$v], 'rechargeRMB');

                    $data[$v]['total_recharge'] = array_sum($recharge_arr);//消费总量
                    $data[$v]['aver_recharge'] = $data[$v]['total_recharge'] == 0 ? 0 : $data[$v]['total_recharge'] / count(array_unique(array_column($db_data_pay_user[$v], 'userId')));

                    $data[$v]['1y'] = $data[$v]['5y'] = $data[$v]['10y'] = $data[$v]['20y'] = $data[$v]['more'] = 0;

                    foreach ($recharge_arr as $value) {
                        if ($value < 100) {
                            $data[$v]['1y']++;
                        } elseif ($value < 500) {
                            $data[$v]['5y']++;
                        } elseif ($value < 1000) {
                            $data[$v]['10y']++;
                        } elseif ($value < 2000) {
                            $data[$v]['20y']++;
                        } else {
                            $data[$v]['more']++;
                        }
                    }
                }
            }
        } elseif (request()->get('type-date') == 2) { //按段显示
            $k = date('Y-m-d', $this->getTime()[0] / 1000) . ' 至 ' . date('Y-m-d', $this->getTime()[1] / 1000);

            $recharge_arr = array_column($db_data_pay_user, 'rechargeRMB');

            $data[$k]['total_recharge'] = array_sum($recharge_arr);//消费总量
            $data[$k]['aver_recharge'] = $data[$k]['total_recharge'] == 0 ? 0 : round($data[$k]['total_recharge'] / count(array_unique(array_column($db_data_pay_user, 'userId'))), 3);//消费总量

            $data[$k]['1y'] = $data[$k]['5y'] = $data[$k]['10y'] = $data[$k]['20y'] = $data[$k]['more'] = 0;

            foreach ($recharge_arr as $v) {
                if ($v < 1) {
                    $data[$k]['1y']++;
                } elseif ($v < 5) {
                    $data[$k]['5y']++;
                } elseif ($v < 10) {
                    $data[$k]['10y']++;
                } elseif ($v < 20) {
                    $data[$k]['20y']++;
                } else {
                    $data[$k]['more']++;
                }
            }
        }

        //把数据存储到文件中 方便导出和画图形
        $this->dataFile('save', 'data.txt', $data);

        return $this->standard_return_view('user.rechargelimit', $data);
    }

    //充值次数
    public function rechargepeople()
    {
        if (request()->status == 2) {//导出exl
            $this->exportExcel(['日期', '总消费量', '人均消费量', '[0-1]消费人数', '[1-5]消费人数', '[5-10]消费人数', '[10-20]消费人数', '[20+]消费人数'], '充值额度');
            return;
        } elseif (request()->status == 3) {//图像化显示
            $data = $this->dataFile('read', 'data.txt');

            $tmp_key = array_keys($data);
            $graph['x'] = '';
            foreach ($tmp_key as $v) {
                $graph['x'] .= "'" . $v . "'" . ',';
            }
            $x = rtrim($graph['x'], ',');

            $total_recharge = implode(',', array_column($data, 'total_recharge'));
            $total_recharge = "{'name':'总消费量',data:[" . $total_recharge . "]}";

            $aver_recharge = implode(',', array_column($data, 'aver_recharge'));
            $aver_recharge = "{'name':'人均消费量',data:[" . $aver_recharge . "]}";

            $y = $total_recharge . ',' . $aver_recharge;

            $graph = $this->discount_data(' 消费额度', $x, $y, '时间', '元');

            return view('graph.line', ['data' => $graph]);
        } elseif (request()->status == 4) {//图像化显示
            $data = $this->dataFile('read', 'data.txt');

            $tmp_key = array_keys($data);
            $graph['x'] = '';
            foreach ($tmp_key as $v) {
                $graph['x'] .= "'" . $v . "'" . ',';
            }
            $x = rtrim($graph['x'], ',');

            $active_rat = implode(',', array_column($data, 'active_rat'));
            $active_rat = "{'name':'活跃用户',data:[" . $active_rat . "]}";

            $y = $active_rat;

            $graph = $this->discount_data(' 活跃用户增幅', $x, $y, '时间', '人数');

            return view('graph.line', ['data' => $graph]);
        }

        //查询今日新增充值用户
        $db_head = $this->getDoc('log_coll_recharge')
            ->select('userId', 'payTime', 'rechargeRMB')
            ->where('logTime', '>=', $this->getTime()[0])
            ->where('logTime', '<=', $this->getTime()[1]);
        if ((int)request()->pid) {
            $db_head = $db_head->where('pid', (int)request()->pid);
        }
        if (request()->serverId) {
            $db_head = $db_head->where('serverId', (int)request()->serverId);
        }
        $db_data_pay_user = $db_head->get()->toArray();


        $data = [];
        if (request()->get('type-date') == 1) { //按日显示
            $db_data_pay_user = $this->rearrayToTime($db_data_pay_user, 'payTime');

            //获取时间列表
            $this_date = $this->generationTimeSeries($this->getTime()[0], $this->getTime()[1]);

            $tmp = [];
            foreach ($this_date as $v) {

                $data[$v]['recharge_user_sum'] = $data[$v]['recharge_sum'] = $data[$v]['aver_recharge'] = $data[$v]['1c'] = $data[$v]['2c'] = $data[$v]['3c'] = $data[$v]['4c'] = $data[$v]['5c'] = $data[$v]['6c'] = $data[$v]['7c'] = $data[$v]['8c'] = $data[$v]['9c'] = $data[$v]['10c'] = $data[$v]['15c'] = $data[$v]['20c'] = $data[$v]['more'] = 0;

                if (isset($db_data_pay_user[$v])) {

                    $recharge_user = array_column($db_data_pay_user[$v], 'userId');
                    $data[$v]['recharge_user_sum'] = count(array_unique($recharge_user));
                    $data[$v]['recharge_sum'] = $data[$v]['recharge_user_sum'] == 0 ? 0 : round(count($recharge_user) / $data[$v]['recharge_user_sum'], 3); //平均充值次数
                    $data[$v]['aver_recharge'] = $data[$v]['recharge_user_sum'] == 0 ? 0 : round(array_sum(array_column($db_data_pay_user[$v], 'rechargeRMB')) / count($recharge_user), 3);

                    $recharge_time = array_count_values($recharge_user);

                    foreach ($recharge_time as $value) {
                        if ($value == 1) {
                            $data[$v]['1c']++;
                        } elseif ($value == 2) {
                            $data[$v]['2c']++;
                        } elseif ($value == 3) {
                            $data[$v]['3c']++;
                        } elseif ($value == 4) {
                            $data[$v]['4c']++;
                        } elseif ($value == 5) {
                            $data[$v]['5c']++;
                        } elseif ($value == 6) {
                            $data[$v]['6c']++;
                        } elseif ($value == 7) {
                            $data[$v]['7c']++;
                        } elseif ($value == 8) {
                            $data[$v]['8c']++;
                        } elseif ($value == 9) {
                            $data[$v]['9c']++;
                        } elseif ($value == 10) {
                            $data[$v]['10c']++;
                        } elseif ($value >= 11 && $value < 15) {
                            $data[$v]['15c']++;
                        } elseif ($value >= 2 && $value < 20) {
                            $data[$v]['20c']++;
                        } else {
                            $data[$v]['more']++;
                        }
                    }
                }
            }
        } elseif (request()->get('type-date') == 2) { //按段显示
            $k = date('Y-m-d', $this->getTime()[0] / 1000) . ' 至 ' . date('Y-m-d', $this->getTime()[1] / 1000);

            $recharge_user = array_column($db_data_pay_user, 'userId');
            $data[$k]['recharge_user_sum'] = count(array_unique($recharge_user));
            $data[$k]['recharge_sum'] = $data[$k]['recharge_sum'] == 0 ? 0 : $data[$k]['recharge_user_sum'] / count($recharge_user);
            $data[$k]['aver_recharge'] = $data[$k]['recharge_sum'] == 0 ? 0 : array_sum(array_column($db_data_pay_user, 'rechargeRMB')) / $data[$k]['recharge_sum'];

            $recharge_time = array_count_values($recharge_user);

            $data[$k]['1c'] = $data[$k]['2c'] = $data[$k]['3c'] = $data[$k]['4c'] = $data[$k]['5c'] = $data[$k]['6c'] = $data[$k]['7c'] = $data[$k]['8c'] = $data[$k]['9c'] = $data[$k]['10c'] = $data[$k]['15c'] = $data[$k]['20c'] = $data[$k]['more'] = 0;

            foreach ($recharge_time as $v) {
                if ($v == 1) {
                    $data[$k]['1c']++;
                } elseif ($v == 2) {
                    $data[$k]['2c']++;
                } elseif ($v == 3) {
                    $data[$k]['3c']++;
                } elseif ($v == 4) {
                    $data[$k]['4c']++;
                } elseif ($v == 5) {
                    $data[$k]['5c']++;
                } elseif ($v == 6) {
                    $data[$k]['6c']++;
                } elseif ($v == 7) {
                    $data[$k]['7c']++;
                } elseif ($v == 8) {
                    $data[$k]['8c']++;
                } elseif ($v == 9) {
                    $data[$k]['9c']++;
                } elseif ($v == 10) {
                    $data[$k]['10c']++;
                } elseif ($v >= 11 && $v < 15) {
                    $data[$k]['15c']++;
                } elseif ($v >= 2 && $v < 20) {
                    $data[$k]['20c']++;
                } else {
                    $data[$k]['more']++;
                }
            }
        }

        //把数据存储到文件中 方便导出和画图形
        $this->dataFile('save', 'data.txt', $data);

        return $this->standard_return_view('user.rechargepeople', $data);
    }

    //充值用户流失
    public function paychum()
    {
        //3月采样充值用户
        $user_pay_head = $this->getDoc('log_coll_recharge')
            ->distinct('userId')
            ->where('logTime', '>=', $this->getTime()[1] - $this->day_time * 90)
            ->where('logTime', '<=', $this->getTime()[1]);
        if ((int)request()->pid) {
            $db_head = $user_pay_head->where('pid', (int)request()->pid);
        }
        if (request()->serverId) {
            $db_head = $user_pay_head->where('serverId', (int)request()->serverId);
        }
        $user_pay = $user_pay_head->get()->toArray();

        //查询充值用户登陆时间
        $db_head = $this->getDoc('log_coll_login')
            ->select('userId', 'logTime')
            ->whereIn('userId', $user_pay);
        if ((int)request()->pid) {
            $db_head = $db_head->where('pid', (int)request()->pid);
        }
        if (request()->serverId) {
            $db_head = $db_head->where('serverId', (int)request()->serverId);
        }
        $db_data_login = $db_head->get()->toArray();
        $db_data_login = $this->array_unset_tt($db_data_login, 'userId');

        $week = $doubweek = $month = 0;
        foreach ($db_data_login as $v) {
            if ($v['logTime'] <= time() * 1000 - $this->day_time * 7) {
                $week++;
            }
            if ($v['logTime'] <= time() * 1000 - $this->day_time * 14) {
                $doubweek++;
            }
            if ($v['logTime'] <= time() * 1000 - $this->day_time * 30) {
                $month++;
            }
        }

        $data['time'] = date('Y-m-d', $this->getTime()[1] / 1000);
        $data['week'] = $week;
        $data['doubweek'] = $doubweek;
        $data['month'] = $month;

        return $this->standard_return_view('user.paychum', $data);
    }

    //充值用户回流
    public function paybackflow()
    {
        //3月采样充值用户
        $user_pay_head = $this->getDoc('log_coll_recharge')
            ->distinct('userId')
            ->where('logTime', '>=', $this->getTime()[1] - $this->day_time * 90)
            ->where('logTime', '<=', $this->getTime()[1]);
        if ((int)request()->pid) {
            $db_head = $user_pay_head->where('pid', (int)request()->pid);
        }
        if (request()->serverId) {
            $db_head = $user_pay_head->where('serverId', (int)request()->serverId);
        }
        $user_pay = $user_pay_head->get()->toArray();

        //充值用户登陆
        $db_head = $this->getDoc('log_coll_login')
            ->select('userId', 'logTime')
            ->whereIn('userId', $user_pay);
        if ((int)request()->pid) {
            $db_head = $db_head->where('pid', (int)request()->pid);
        }
        if (request()->serverId) {
            $db_head = $db_head->where('serverId', (int)request()->serverId);
        }
        $db_data_login = $db_head->orderBy('logTime', 'desc')->get()->toArray();
        $db_data_login = $this->array_unset_tt($db_data_login, 'userId');

        $week = $doubweek = $month = [];
        foreach ($db_data_login as $v) {
            if ($v['logTime'] <= time() * 1000 - $this->day_time * 7) {
                $week[] = $v['userId'];
            }

            if ($v['logTime'] >= time() * 1000 - $this->day_time * 14) {
                $doubweek[] = $v['userId'];
            }

            if ($v['logTime'] >= time() * 1000 - $this->day_time * 30) {
                $month[] = $v['userId'];
            }
        }

        //查询今天登陆的用户
        $db_head = $this->getDoc('log_coll_login')
            ->distinct('userId')
            ->where('logTime', '>=', $this->getTime()[1] - $this->day_time)
            ->where('logTime', '<=', $this->getTime()[1]);
        if ((int)request()->pid) {
            $db_head = $db_head->where('pid', (int)request()->pid);
        }
        if (request()->serverId) {
            $db_head = $db_head->where('serverId', (int)request()->serverId);
        }
        $db_data_login_day = $db_head->get()->toArray();

        $data['time'] = date('Y-m-d', $this->getTime()[1] / 1000);
        $data['week'] = count(array_intersect($week, $db_data_login_day));
        $data['doubweek'] = count(array_intersect($week, $doubweek));
        $data['month'] = count(array_intersect($week, $month));

        return $this->standard_return_view('user.paybackflow', $data);
    }
}
