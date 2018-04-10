<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class incomeController extends Controller
{
    //每日充值总况
    public function paytotal()
    {
        if (request()->status == 2) {//导出exl
            $this->exportExcel(['统计时间', '活跃用户数', '充值用户数', '充值总额', '付费次数'], '充值总况');
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
                    $y = implode(',', array_column($data, 'user_pay_sum'));
                    $y = "{'name':'充值用户数',data:[" . $y . "]}";

                    $graph = $this->discount_data('充值用户数', $x, $y, '时间', '');
                    break;
                case 4:
                    $y = implode(',', array_column($data, 'pay_rat'));
                    $y = "{'name':'付费率',data:[" . $y . "]}";

                    $graph = $this->discount_data('付费率', $x, $y, '时间', '');
                    break;
                case 5:
                    $pay_arpu = implode(',', array_column($data, 'pay_arpu'));
                    $pay_arpu = "{'name':'付费ARPU',data:[" . $pay_arpu . "]}";

                    $active_arpu = implode(',', array_column($data, 'active_arpu'));
                    $active_arpu = "{'name':'活跃ARPU',data:[" . $active_arpu . "]}";

                    $y = $pay_arpu . ',' . $active_arpu;
                    $graph = $this->discount_data('ARPU', $x, $y, '时间', '');

                    break;
            }

            return view('graph.line', ['data' => $graph, 'title' => '充值总况']);
        }

        //登录游戏的用户量
        $login_data_distinct = $this->getDoc('log_coll_login')
            ->select('userId', 'logTime')
            ->where('logTime', '>=', $this->getTime()[0])
            ->where('logTime', '<=', $this->getTime()[1]);

        if ((int)request()->pid) {
            $login_data_distinct = $login_data_distinct->where('pid', (int)request()->pid);
        }
        if ((int)request()->serverId) {
            $login_data_distinct = $login_data_distinct->where('serverId', (int)request()->serverId);
        }
        $login_data_distinct = $login_data_distinct->get()->toArray();

        //创建角色数量
        $create_data = $this->getDoc('log_coll_create_role')
            ->select('userId', 'logTime')
            ->where('logTime', '>=', $this->getTime()[0])
            ->where('logTime', '<=', $this->getTime()[1]);
        if ((int)request()->pid) {
            $create_data = $create_data->where('pid', (int)request()->pid);
        }
        if ((int)request()->serverId) {
            $create_data = $create_data->where('serverId', (int)request()->serverId);
        }
        $create_data = $create_data->get()->toArray();

        //查询用户付费
        $login_data_recharge = $this->getDoc('log_coll_recharge')->select(
            'userId', 'rechargeRMB', 'logTime'
        )
            ->where('payTime', '>=', $this->getTime()[0])
            ->where('payTime', '<=', $this->getTime()[1]);
        if ((int)request()->pid) {
            $login_data_recharge = $login_data_recharge->where('pid', (int)request()->pid);
        }
        if ((int)request()->serverId) {
            $login_data_recharge = $login_data_recharge->where('serverId', (int)request()->serverId);
        }
        $login_data_recharge = $login_data_recharge->get()->toArray();

        $data = [];
        if (request()->get('type-date') == 1) { //按日显示
            $login_data_distinct = $this->rearrayToTime($login_data_distinct, 'logTime');//按时间分组
            $create_data = $this->rearrayToTime($create_data, 'logTime');//按时间分组
            $login_data_recharge = $this->rearrayToTime($login_data_recharge, 'logTime');//按时间分组

            foreach ($login_data_distinct as $k => $v) {
                //用户去重
                $v = array_unique(array_column($v, 'userId'));

                $data[$k]['active'] = $data[$k]['user_pay'] = $data[$k]['user_pay_sum'] = $data[$k]['pay_money'] = $data[$k]['pay_sum'] = $data[$k]['pay_arpu'] = $data[$k]['active_arpu'] = $data[$k]['pay_rat'] = 0;
                $data[$k]['active'] = count(array_diff($v, array_unique(array_column($create_data, 'userId'))));

                if (isset($login_data_recharge[$k])) {
                    $data[$k]['user_pay'] = array_unique(array_column($login_data_recharge[$k], 'userId')); //充值用户userId
                    $data[$k]['user_pay_sum'] = count($data[$k]['user_pay']); // 充值用户总数
                    $data[$k]['pay_money'] = array_sum(array_column($login_data_recharge[$k], 'rechargeRMB')); //充值总金额
                    $data[$k]['pay_sum'] = count($login_data_recharge[$k]); //充值次数
                    $data[$k]['pay_rat'] = $data[$k]['active'] == 0 ? 0 : round($data[$k]['user_pay_sum'] / $data[$k]['active'], 3);//付费率
                    $data[$k]['pay_arpu'] = $data[$k]['pay_money'] == 0 ? 0 : round($data[$k]['pay_money'] / $data[$k]['user_pay_sum'], 3); //付费ARPU
                    $data[$k]['active_arpu'] = $data[$k]['pay_money'] == 0 ? 0 : round($data[$k]['pay_money'] / $data[$k]['active'], 3);//活跃ARPU
                }
            }
            //后期处理 没有的补上
            $tmp = [
                'active' => 0,
                'user_pay' => 0,
                'user_pay_sum' => 0,
                'pay_money' => 0,
                'pay_sum' => 0,
                'pay_rat' => 0,
                'pay_arpu' => 0,
                'active_arpu' => 0
            ];
            $this->fill($data, $tmp);
        } else if (request()->get('type-date') == 2) { //按时间段显示
            $k = date('Y-m-d', $this->getTime()[0] / 1000) . ' 至 ' . date('Y-m-d', $this->getTime()[1] / 1000);
            $active = 0; //活跃用户量
            foreach ($login_data_distinct as $v) {
                if (!in_array($v, $create_data)) {
                    $active++;
                }
            }
            $data[$k]['user_pay'] = array_unique(array_column($login_data_recharge, 'userId')); //充值用户数
            $data[$k]['user_pay_sum'] = count($data['user_pay']); // 充值用户总数
            $data[$k]['pay_money'] = array_sum(array_column($login_data_recharge, 'rechargeRMB')); //充值总金额
            $data[$k]['pay_sum'] = count($login_data_recharge); //充值次数
            $data[$k]['pay_arpu'] = $data['pay_sum'] ? round($data['pay_money'] / $data['pay_sum'], 2) : 0;
            $data[$k]['active_arpu'] = round($data['pay_money'] / ($active ? $active : 1), 2);
            $data[$k]['pay_rat'] = round(count($data['user_pay']) / ($active ? $active : 1), 2);
            $data[$k]['active'] = $active;
        }

        //把数据存储到文件中 方便导出和画图形
        $this->dataFile('save', 'data.txt', $data);

        return $this->standard_return_view('income.paytotal', $data);
    }

    //实时充值总况
    public function timelypay()
    {
        if (request()->status == 2) {//导出exl
            $this->exportExcel(['统计时间', '活跃用户数', '充值用户数', '充值总额', '付费次数'], '充值总况');
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
                    $y = implode(',', array_column($data, 'user_pay_sum'));
                    $y = "{'name':'充值用户数',data:[" . $y . "]}";

                    $graph = $this->discount_data('充值用户数', $x, $y, '时间', '');
                    break;
                case 4:
                    $y = implode(',', array_column($data, 'pay_rat'));
                    $y = "{'name':'付费率',data:[" . $y . "]}";

                    $graph = $this->discount_data('付费率', $x, $y, '时间', '');
                    break;
                case 5:
                    $pay_arpu = implode(',', array_column($data, 'pay_arpu'));
                    $pay_arpu = "{'name':'付费ARPU',data:[" . $pay_arpu . "]}";

                    $active_arpu = implode(',', array_column($data, 'active_arpu'));
                    $active_arpu = "{'name':'活跃ARPU',data:[" . $active_arpu . "]}";

                    $y = $pay_arpu . ',' . $active_arpu;
                    $graph = $this->discount_data('ARPU', $x, $y, '时间', '');

                    break;
            }

            return view('graph.line', ['data' => $graph, 'title' => '充值总况']);
        }

        //去重登录游戏的用户量
        $login_data_distinct = $this->getDoc('log_coll_login')
            ->select('userId', 'logTime')
            ->where('logTime', '>=', $this->getTime()[0])
            ->where('logTime', '<=', $this->getTime()[1]);
        if ((int)request()->pid) {
            $login_data_distinct = $login_data_distinct->where('pid', (int)request()->pid);
        }
        if ((int)request()->serverId) {
            $login_data_distinct = $login_data_distinct->where('serverId', (int)request()->serverId * 1);
        }
        $login_data_distinct = $login_data_distinct->get()->toArray();

        //创建角色数量量
        $create_data = $this->getDoc('log_coll_create_role')
            ->select('userId', 'logTime')
            ->where('logTime', '>=', $this->getTime()[0])
            ->where('logTime', '<=', $this->getTime()[1]);
        if ((int)request()->pid) {
            $create_data = $create_data->where('pid', (int)request()->pid);
        }
        if ((int)request()->serverId) {
            $create_data = $create_data->where('serverId', (int)request()->serverId * 1);
        }

        $create_data = $create_data->get()->toArray();

        $login_data_recharge = $this->getDoc('log_coll_recharge')->select(
            'userId', 'rechargeRMB', 'payTime'
        )
            ->where('payTime', '>=', $this->getTime()[0])
            ->where('payTime', '<=', $this->getTime()[1]);

        if ((int)request()->pid) {
            $login_data_recharge = $login_data_recharge->where('pid', (int)request()->pid);
        }
        if ((int)request()->serverId) {
            $login_data_recharge = $login_data_recharge->where('serverId', (int)request()->serverId * 1);
        }

        $login_data_recharge = $login_data_recharge->get()->toArray();

        $create_data = array_column($create_data, 'userId');
        $create_data_dis = array_column($create_data, 'userId');

        $data = [];
        foreach ($login_data_distinct as $v) {
            $hour = date('H', $v['logTime']);
            $data[$hour] = [
                'active' => 0, //活跃用户数
                'user_pay' => 0, //充值用户数 去重
                'user_pay_sum' => 0, //充值用户数 不去重
                'pay_money' => 0, //总金额
                'pay_sum' => 0, //付费次数
                'pay_arpu' => 0,
                'active_arpu' => 0,
                'pay_rat' => 0
            ];
            $data[$hour]['active'] = count(array_unique(array_diff($v, $create_data_dis)));
        }

        foreach ($login_data_recharge as $v) {
            $hour = date('H', $v['payTime']);
            if (isset($data[$hour])) {
                $data[$hour] = [
                    'active' => 0, //活跃用户数
                    'user_pay' => 0, //充值用户数 去重
                    'user_pay_sum' => 0, //充值用户数 不去重
                    'pay_money' => 0, //总金额
                    'pay_sum' => 0, //付费次数
                    'pay_arpu' => 0,
                    'active_arpu' => 0,
                    'pay_rat' => 0
                ];
            }
            $data[$hour]['user_pay_sum'] += 1;
            $data[$hour]['pay_money'] += $v['rechargeRMB'];
            $data[$hour]['pay_sum'] += 1;
            $data[$hour]['pay_rat'] = round($data[$hour]['user_pay_sum'] / ($data[$hour]['active'] ? $data[$hour]['active'] : 1), 2);
            $data[$hour]['pay_money'] += $v['rechargeRMB'];
            $data[$hour]['pay_arpu'] = $data[$hour]['pay_money'] == 0 ? 0 : round($data[$hour]['pay_money'] / $data[$hour]['pay_sum'], 2);
            $data[$hour]['active_arpu'] = $data[$hour]['pay_money'] == 0 || $data[$hour]['active'] == 0 ? 0 : round($data[$hour]['pay_money'] / $data[$hour]['active'], 2);
        }

        ksort($data);

        //把数据存储到文件中 方便导出和画图形
        $this->dataFile('save', 'data.txt', $data);

        return $this->standard_return_view('income.timelypay', $data);
    }

    //LTV值
    public function payKTV()
    {
        if (request()->status == 2) {//导出exl
            $this->exportExcel(['统计时间', 'LTV'], 'LTV');
            return;
        } elseif (request()->status == 3) {//图像化显示
            $data = $this->dataFile('read', 'data.txt');

            $tmp_key = array_keys($data);
            $graph['x'] = '';
            foreach ($tmp_key as $v) {
                $graph['x'] .= "'" . $v . "'" . ',';
            }
            $x = rtrim($graph['x'], ',');

            $y = implode(',', array_column($data, 'ltv'));
            $y = "{'name':'LTV',data:[" . $y . "]}";

            $graph = $this->discount_data('LTV', $x, $y, '时间', '');

            return view('graph.line', ['data' => $graph, 'title' => 'LTV']);
        }

        //查询新建的用户
        $create_user = $this->getDoc('log_coll_create_user')
            ->select('userId', 'logTime')
            ->where('logTime', '>=', $this->getTime()[0])
            ->where('logTime', '<=', $this->getTime()[1]);

        if ((int)request()->pid) {
            $create_user = $create_user->where('pid', (int)request()->pid);
        }
        $create_user = $create_user->get()->toArray();

        //根据用户id查询付费用户
        $create_user_pay = $this->getDoc('log_coll_recharge')
            ->select('rechargeRMB', 'logTime')
            ->whereIn('userId', array_column($create_user, 'userId'))
            ->get()->toArray();

        $create_user = $this->rearrayToTime($create_user, 'logTime');//按时间分组
        $create_user_pay = $this->rearrayToTime($create_user_pay, 'logTime');//按时间分组

        $data = [];
        foreach ($create_user as $k => $v) {
            if (isset($create_user_pay[$k]) && count($v) != 0) {
                $data[$k]['ltv'] = round(array_sum(array_column($create_user_pay[$k], 'rechargeRMB')) / count($v), 3);
            } else {
                $data[$k]['ltv'] = 0;
            }
        }
        $this->fill($data, ['ltv' => 0]);

        //把数据存储到文件中 方便导出和画图形
        $this->dataFile('save', 'data.txt', $data);

        return $this->standard_return_view('income.ltv', $data);
    }

    //付费分布
    public function paydistribution()
    {
        $data = [];
        switch (request()->get('type')) {
            case 1:
                $channel_pay = $this->getDoc('log_coll_recharge')
                    ->select('rechargeRMB', 'logTime')
                    ->where('logTime', '>=', $this->getTime()[0])
                    ->where('logTime', '<=', $this->getTime()[1])
                    ->get()->toArray();

                $channel_pay_data = [];
                foreach ($channel_pay as $k => $v) {
                    $channel_pay_data[$v['logTime']][] = $v;
                }

                $tatal_money = array_sum(array_column($channel_pay, 'rechargeRMB'));

                foreach ($channel_pay_data as $k => $v) {
                    $v_total_money = array_sum(array_column($v, 'rechargeRMB'));
                    $data[$k] = [
                        'time' => $k,
                        'money' => $v_total_money,
                        'rat' => round($v_total_money / $tatal_money, 2)
                    ];
                }
                break;
            case 2:
                $channel_pay = $this->getDoc('log_coll_recharge')
                    ->select('rechargeRMB', 'pid')
                    ->where('logTime', '>=', $this->getTime()[0])
                    ->where('logTime', '<=', $this->getTime()[1])
                    ->get()->toArray();

                $channel_pay_data = [];
                foreach ($channel_pay as $k => $v) {
                    $channel_pay_data[$v['pid']][] = $v;
                }

                $tatal_money = array_sum(array_column($channel_pay, 'rechargeRMB'));

                foreach ($channel_pay_data as $k => $v) {
                    $v_total_money = array_sum(array_column($v, 'rechargeRMB'));
                    $data[$k] = [
                        'id' => $k,
                        'money' => $v_total_money,
                        'rat' => round($v_total_money / $tatal_money, 2)
                    ];
                }
                break;
            case 3:
                $channel_pay = $this->getDoc('log_coll_recharge')
                    ->select('rechargeRMB', 'serverId')
                    ->where('logTime', '>=', $this->getTime()[0])
                    ->where('logTime', '<=', $this->getTime()[1])
                    ->get()->toArray();

                $channel_pay_data = [];
                foreach ($channel_pay as $k => $v) {
                    $channel_pay_data[$v['serverId']][] = $v;
                }

                $tatal_money = array_sum(array_column($channel_pay, 'rechargeRMB'));

                foreach ($channel_pay_data as $k => $v) {
                    $v_total_money = array_sum(array_column($v, 'rechargeRMB'));
                    $data[$k] = [
                        'id' => $this->getServer()[$k],
                        'money' => $v_total_money,
                        'rat' => round($v_total_money / $tatal_money, 2)
                    ];
                }
                break;
        }

        return view('income.paydistribution', [
            'data' => $data,
            'start' => $this->getTime()[0],
            'end' => $this->getTime()[1],
            'option' => request()->get('option-date') ? request()->get('option-date') : 1,
            'type' => request()->get('type') ? request()->get('type') : 1,
        ]);
    }

    //玩家付费排行榜
    public function userPayment()
    {
        if (request()->status == 2) {//导出exl
            $this->exportExcel(['用户id', '用户id', '充值金额'], '充值TOP');
            return;
        }

        //查询玩家付费情况
        $pay_user = $this->getDoc('log_coll_recharge')
            ->select(
                'rechargeRMB', 'userId', 'payTime'
            )
            ->where('logTime', '>=', $this->getTime()[0])
            ->where('logTime', '<=', $this->getTime()[1]);

        if ((int)request()->pid) {
            $pay_user = $pay_user->where('pid', (int)request()->pid);
        }
        if ((int)request()->serverId) {
            $pay_user = $pay_user->where('serverId', (int)request()->serverId * 1);
        }

        $pay_user = $pay_user->get()->toArray();

        $data = [];
        if (!empty($pay_user)) {
            $user_pay = [];

            //归并玩家付费
            foreach ($pay_user as $v) {
                $user_pay[$v['userId']][] = $v;
            }

            foreach ($user_pay as $k => $v) {
                $data[$k]['userId'] = $k;
                $data[$k]['money'] = array_sum(array_column($v, 'rechargeRMB'));
            }
        }

        //把数据存储到文件中 方便导出和画图形
        $this->dataFile('save', 'data.txt', $data);

        return $this->standard_return_view('income.userPayment', $data);
    }

    public function paydetail()
    {
        if (request()->status == 2) {//导出exl
            $data = $this->dataFile('read', 'data.txt');

            $head = [['充值时间点', '账号', '渠道ID', '服务器ID', '角色ID', '角色名', '充值金额']];
            foreach ($data as $k => $v) {
                $data[$k] = [
                    date('Y-m-d H:i:s', $v['payTime']),
                    $v['userId'],
                    $v['pid'],
                    $v['serverId'],
                    $v['roleId'],
                    $v['roleName'],
                    $v['rechargeRMB'],
                ];
            }
            $data = array_merge($head, $data);

            $this->exceel('充值明细', $data);

            return;
        }

        //查询玩家付费情况
        $data = $this->getDoc('log_coll_recharge')
            ->select(
                'payTime', 'userId', 'pid', 'serverId', 'roleId', 'roleName', 'rechargeRMB'
            )
            ->where('logTime', '>=', $this->getTime()[0])
            ->where('logTime', '<=', $this->getTime()[1]);

        if ((int)request()->pid) {
            $data = $data->where('pid', (int)request()->pid);
        }
        if ((int)request()->serverId) {
            $data = $data->where('serverId', (int)request()->serverId * 1);
        }

        $data = $data->get()->toArray();

        //把数据存储到文件中 方便导出和画图形
        $this->dataFile('save', 'data.txt', $data);


        return $this->standard_return_view('income.detailPayment', $data);
    }
}
