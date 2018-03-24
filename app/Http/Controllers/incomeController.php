<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class incomeController extends Controller
{
    public function paytotal()//每日充值总况
    {
        if (request()->start) {
            $start = strtotime(request()->start) * 1000;

            if (request()->end) {
                $end = request()->end;
            } else {
                $end = $start + 86400000;
            }
        } else {
            //查询出最近一条
            $newest_data = $this->getDoc('log_coll_recharge')->select(
                'payTime'
            )->orderBy('payTime', 'desc')->first();

            $end = $newest_data['payTime'];
            $start = strtotime(date('Y-m-d', $end / 1000)) * 1000;
        }

        //去重登录游戏的用户量
        $login_data_distinct = $this->getDoc('log_coll_login')
            ->distinct('roleId', 'userId')
            ->where('logTime', '>=', $start)
            ->where('logTime', '<=', $end)
            ->get();

        //创建角色数量量
        $create_data = $this->getDoc('log_coll_create_role')
            ->select('roleId')
            ->where('logTime', '>=', $start)
            ->where('logTime', '<=', $end)
            ->get()->toArray();

        $create_data = array_column($create_data, 'roleId');

        $active = 0; //活跃用户量

        foreach ($login_data_distinct as $v) {
            if (!in_array($v['userId'], $create_data)) {
                $active++;
            }
        }

        $login_data_recharge = $this->getDoc('log_coll_recharge')->select(
            'userId', 'rechargeRMB'
        )
            ->where('payTime', '>=', $start)
            ->where('payTime', '<=', $end)
            ->get()->toArray();

        $data['user_pay'] = array_unique(array_column($login_data_recharge, 'userId')); //充值用户数
        $data['user_pay_sum'] = count($data['user_pay']);
        $data['pay_money'] = array_sum(array_column($login_data_recharge, 'rechargeRMB'));
        $data['pay_sum'] = count($login_data_recharge);
        $data['pay_arpu'] = $data['pay_sum'] ? round($data['pay_money'] / $data['pay_sum'], 2) : 0;
        $data['active_arpu'] = round($data['pay_money'] / ($active ? $active : 1), 2);
        $data['pay_rat'] = round(count($data['user_pay']) / ($active ? $active : 1), 2);
        $data['active'] = $active;

        return view('income.paytotal', ['data' => $data]);
    }

    //每小时充值总况
    public function timelypay()
    {
        if (request()->start) {
            $start = strtotime(request()->start) * 1000;
            if (request()->end) {
                $end = request()->end;
            } else {
                $end = $start + 86400000;
            }
        } else {
            //查询出最近一条
            $newest_data = $this->getDoc('log_coll_recharge')->select(
                'payTime'
            )->orderBy('payTime', 'desc')->first();

            $end = $newest_data['payTime'];
            $start = strtotime(date('Y-m-d', $end / 1000)) * 1000;
        }

        //去重登录游戏的用户量
        $login_data_distinct = $this->getDoc('log_coll_login')
            ->distinct('roleId', 'userId', 'logTime')
            ->where('logTime', '>=', $start)
            ->where('logTime', '<=', $end)
            ->get()->toArray();

        //创建角色数量量
        $create_data = $this->getDoc('log_coll_create_role')
            ->select('roleId')
            ->where('logTime', '>=', $start)
            ->where('logTime', '<=', $end)
            ->get()->toArray();

        $create_data = array_column($create_data, 'roleId');
        $log_data = array_column($create_data, 'logTime');

        $data[0] = [
            'active' => 0, //活跃用户数
            'user_pay' => 0, //充值用户数 去重
            'user_pay_sum' => 0, //充值用户数 不去重
            'pay_money' => 0, //总金额
            'pay_sum' => 0, //付费次数
            'pay_arpu' => 0,
            'active_arpu' => 0,
            'pay_rat' => 0
        ];

        foreach ($login_data_distinct as $v) {
            $index = array_search($v['userId'], $create_data);
            if (!$index) {
                $hour = date('H', $v['logTime']);

                $data[$hour]['active'] += 1;
            }
        }

        $login_data_recharge = $this->getDoc('log_coll_recharge')->select(
            'userId', 'rechargeRMB', 'payTime'
        )
            ->where('payTime', '>=', $start)
            ->where('payTime', '<=', $end)
            ->get()->toArray();

        foreach ($login_data_recharge as $v) {
            $hour = (int)date('H', $v['payTime']);
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

            $data[$hour]['user_pay_sum'] += 1;
            $data[$hour]['pay_money'] += $v['rechargeRMB'];
            $data[$hour]['pay_sum'] += 1;
            $data[$hour]['pay_rat'] = round($data[$hour]['user_pay_sum'] / ($data[$hour]['active'] ? $data[$hour]['active'] : 1), 2);
            $data[$hour]['pay_money'] += $v['rechargeRMB'];
            $data[$hour]['pay_arpu'] = $data[$hour]['pay_sum'] ? round($data[$hour]['pay_money'] / $data[$hour]['pay_sum'], 2) : 0;
            $data[$hour]['active_arpu'] = round($data[$hour]['pay_money'] / ($data[$hour]['active'] ? $data[$hour]['active'] : 1), 2);
        }
        ksort($data);

        return view('income.timelypay', ['data' => $data]);
    }

    //LTV值
    public function payKTV()
    {
        if (request()->start) {
            $start = strtotime(request()->start) * 1000;

            $end = $start + 86400000;
        } else {
            //查询出最近一条
            $newest_data = $this->getDoc('log_coll_create_user')->select(
                'logTime'
            )->orderBy('logTime', 'desc')->first();

            $end = $newest_data['logTime'];
            $start = strtotime(date('Y-m-d', $end / 1000)) * 1000;
        }

        //查询新建的用户
        $create_user = $this->getDoc('log_coll_create_user')
            ->distinct('userId')
            ->where('logTime', '>=', $start)
            ->where('logTime', '<=', $end)
            ->get()->toArray();

        //根据用户id查询付费用户
        $create_user_pay = $this->getDoc('log_coll_recharge')
            ->select('rechargeRMB')
            ->whereIn('userId', $create_user)
            ->get()->toArray();

        $data = array_sum(array_column($create_user_pay, 'rechargeRMB')) / count($create_user);

        return view('income.ltv', ['data' => $data]);
    }

    public function pointPayment()
    {


        return [123];
    }

    //渠道平台付费分布
    public function channelPayment()
    {
        $channel_pay = $this->getDoc('log_coll_recharge')
            ->select('rechargeRMB', 'pid')
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

        return view('income.chanelPayment', ['data' => $data]);
    }

    //服务器付费分布
    public function serverPayment()
    {
        $channel_pay = $this->getDoc('log_coll_recharge')
            ->select('rechargeRMB', 'serverId')
            ->get()->toArray();

        $channel_pay_data = [];
        foreach ($channel_pay as $k => $v) {
            $channel_pay_data[$v['serverId']][] = $v;
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

        return view('income.serverPayment', ['data' => $data]);
    }

    //玩家付费排行榜
    public function userPayment()
    {
        if (request()->start) {
            $start = strtotime(request()->start) * 1000;

            $end = $start + 86400000;
        } else {
            //查询出最近一条
            $newest_data = $this->getDoc('log_coll_recharge')->select(
                'payTime'
            )->orderBy('payTime', 'desc')->first();

            $end = $newest_data['payTime'];
            $start = strtotime(date('Y-m-d', $end / 1000)) * 1000;
        }

        //查询玩家付费情况
        $pay_user = $this->getDoc('log_coll_recharge')
            ->select(
                'rechargeRMB', 'userId'
            )
            ->where('logTime', '>=', $start)
            ->where('logTime', '<=', $end)
            ->get()->toArray();

        $user_data = [];
        if (!empty($pay_user)) {
            $user_pay = [];

            foreach ($pay_user as $v) {
                $user_pay[$v['userId']][] = $v;
            }

            foreach ($user_pay as $k => $v) {
                $user_data[$k]['userId'] = $k;
                $user_data[$k]['money'] = array_sum(array_column($v, 'rechargeRMB'));
            }

            //根据指定字段进行排行
            array_multisort(array_column($user_data, 'money'), SORT_DESC, $user_data);
        }

        return view('income.userPayment', ['data' => $user_data]);
    }

    public function paydetail()
    {
        if (request()->start) {
            $start = strtotime(request()->start) * 1000;

            $end = $start + 86400000;
        } else {
            //查询出最近一条
            $newest_data = $this->getDoc('log_coll_recharge')->select(
                'payTime'
            )->orderBy('payTime', 'desc')->first();

            $end = $newest_data['payTime'];
            $start = strtotime(date('Y-m-d', $end / 1000)) * 1000;
        }

        //查询玩家付费情况
        $data = $this->getDoc('log_coll_recharge')
            ->select(
                'rechargeRMB', 'userId', 'pid','serverId','userId','payTime','roleName','roleId'
            )
            ->where('logTime', '>=', $start)
            ->where('logTime', '<=', $end)
            ->get()->toArray();

        return view('income.detailPayment', ['data' => $data]);
    }
}
