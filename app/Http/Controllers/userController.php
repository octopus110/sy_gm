<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class userController extends Controller
{
    public function total()
    {
        //结束时间 默认现在的毫秒数
        if (request()->end) {
            $end = strtotime(request()->end) * 1000;
        } else {
            $end = time() * 1000;//现在的毫米数
        }

        //开始时间 默认今天凌晨的毫秒数
        if (request()->start) {
            $start = strtotime(request()->start) * 1000;
        } else {
            $start = strtotime(date('Y-m-d', time())) * 1000;
        }

        //查询新增用户数
        $data['new_user'] = $this->getDoc('log_coll_create_user')->select(
            'userId'
        )
            ->where('logTime', '>=', $start)
            ->where('logTime', '<=', $end)
            ->get()->count();

        //登录游戏的用户量
        $login_data = $this->getDoc('log_coll_login')
            ->select('userId')
            ->where('logTime', '>=', $start)
            ->where('logTime', '<=', $end)
            ->get()->toArray();

        //创建角色数量量
        $create_data = $this->getDoc('log_coll_create_role')
            ->select('userId')
            ->where('logTime', '>=', $start)
            ->where('logTime', '<=', $end)
            ->get()->toArray();

        $create_data = array_column($create_data, 'roleId');

        //获取用户量
        $login_data_distinct = array_unique(array_column($login_data, 'userId'));
        $data['active'] = 0;
        foreach ($login_data_distinct as $v) {
            if (!in_array($v, $create_data)) {
                $data['active']++;
            }
        }

        //登录次数
        $data['login_sum'] = count($login_data);

        //人均登录次数
        $data['login_avg_sum'] = count($login_data_distinct) == 0 ? 0 : $data['login_sum'] / count($login_data_distinct);

        //上一天登录数据
        $login_data_pre = $this->getDoc('log_coll_login')
            ->select('userId')
            ->where('logTime', '>=', $start - 86400000)
            ->where('logTime', '<=', $end - 86400000)
            ->get()->count();

        //人均登录次数增幅
        $data['login_avg_rat'] = $login_data_pre == 0 ? $data['login_sum'] : $data['login_sum'] / $login_data_pre;

        //下载量
        $data['downloads'] = 0;

        return view('user.total', ['data' => $data]);
    }

    //实时用户总况
    public function activetotal()
    {
        //结束时间 默认现在的毫秒数
        if (request()->end) {
            $end = strtotime(request()->end) * 1000;
        } else {
            $end = time() * 1000;//现在的毫米数
        }

        //开始时间 默认今天凌晨的毫秒数
        if (request()->start) {
            $start = strtotime(request()->start) * 1000;
        } else {
            $start = strtotime(date('Y-m-d', time())) * 1000;
        }

        //查询新增用户数
        $create_user_data = $this->getDoc('log_coll_create_user')->select(
            'userId', 'logTime'
        )
            ->where('logTime', '>=', $start)
            ->where('logTime', '<=', $end)
            ->get()->toArray();

        $data[0] = [
            'new_user' => 0, //新增用户数
            'active' => 0, //活跃用户数
            'login_sum' => 0, //登录次数
            'login_sum_avg' => 0, //总金额
            'login_rat_avg' => 0, //付费次数
        ];

        foreach ($create_user_data as $v) {
            $hour = date('H', $v['logTime']);
            $data[$hour]['new_user'] = 0;
            $data[$hour]['new_user'] += 1;
        }

        //登录游戏的用户量
        $login_data = $this->getDoc('log_coll_login')
            ->select('userId','loginTime')
            ->where('logTime', '>=', $start)
            ->where('logTime', '<=', $end)
            ->get()->toArray();

        $data[$hour]['login_sum'] = 0;
        foreach ($login_data as $v) {
            $hour = date('H', $v['loginTime']);
            $data[$hour]['login_sum'] += 1;
        }

        //创建角色数量量
        $create_data = $this->getDoc('log_coll_create_role')
            ->select('userId')
            ->where('logTime', '>=', $start)
            ->where('logTime', '<=', $end)
            ->get()->toArray();

        $create_data = array_column($create_data, 'roleId');

        //获取用户量
        $login_data_distinct = array_unique(array_column($login_data, 'userId'));
        $data['active'] = 0;
        foreach ($login_data_distinct as $v) {
            if (!in_array($v, $create_data)) {
                $data['active']++;
            }
        }

        //登录次数
        $data['login_sum'] = count($login_data);

        //人均登录次数
        $data['login_avg_sum'] = count($login_data_distinct) == 0 ? 0 : $data['login_sum'] / count($login_data_distinct);

        //上一天登录数据
        $login_data_pre = $this->getDoc('log_coll_login')
            ->select('userId')
            ->where('logTime', '>=', $start - 86400000)
            ->where('logTime', '<=', $end - 86400000)
            ->get()->count();

        //人均登录次数增幅
        $data['login_avg_rat'] = $login_data_pre == 0 ? $data['login_sum'] : $data['login_sum'] / $login_data_pre;

        //下载量
        $data['downloads'] = 0;

        return view('user.activetotal', ['data' => $data]);
    }
}
