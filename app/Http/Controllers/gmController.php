<?php

namespace App\Http\Controllers;

use function foo\func;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class gmController extends Controller
{
    //登陆前公告
    public function notice()
    {
        $notice = DB::table('t_notice')->whereIn('status', [0, 1])->get()->toArray();

        array_walk($notice, function ($v) {
            $lang = json_decode($v->title, true)['lang'];
            $text = json_decode($v->title, true)['text'];
            $v->title = array_combine($lang, $text);

            $lang = json_decode($v->contant, true)['lang'];
            $text = json_decode($v->contant, true)['text'];
            $v->contant = array_combine($lang, $text);

            if (!$v->language) {
                $v->language = "cn";
            }

            $v->beginTime = date('Y-m-d H:i:s', $v->beginTime / 1000);
            $v->endTime = date('Y-m-d H:i:s', $v->endTime / 1000);

            return $v;
        });

        return view('gm/notice', ['data' => $notice]);
    }

    //登陆前公告编辑
    public function noticeNewEdit(Request $request)
    {
        if ($request->isMethod('get')) {
            if (request()->get('id')) {//修改
                $notice = DB::table('t_notice')->where('id', request()->get('id'))->first();

                $lang = json_decode($notice->title, true)['lang'];
                $text = json_decode($notice->title, true)['text'];
                $notice->title = array_combine($lang, $text);

                $lang = json_decode($notice->contant, true)['lang'];
                $text = json_decode($notice->contant, true)['text'];

                if (!isset($text[0])) {
                    $text[0] = '';
                }

                if (!isset($text[1])) {
                    $text[1] = '';
                }

                $notice->contant = $text[0] . "\n****\n" . $text[1];

                if (!$notice->language) {
                    $notice->language = "cn";
                }

                $notice->beginTime = date('Y-m-d H:i:s', $notice->beginTime / 1000);
                $notice->endTime = date('Y-m-d H:i:s', $notice->endTime / 1000);

                return view('gm/notice_new_edit', ['data' => $notice]);

            } else {//新增
                return view('gm/notice_new_edit');
            }
        } else {
            $this->validate($request, [
                'title_cn' => 'required',
                'weight' => 'required|numeric',
                'beginTime' => 'required',
                'endTime' => 'required',
                'contant' => 'required'
            ]);

            $data = [
                'weight' => $request->get('weight'),
                'beginTime' => strtotime($request->get('beginTime')) * 1000,
                'endTime' => strtotime($request->get('endTime')) * 1000,
                'status' => $request->get('status'),
                'language' => $request->get('language'),
            ];

            if ($request->get('title_en')) {
                $data['title'] = '{"lang":["cn","en"],"text":["' . $request->get('title_cn') . '","' . $request->get('title_en') . '"]}';
            } else {
                $data['title'] = '{"lang":["cn"],"text":["' . $request->get('title_cn') . '"]}';
            }

            $contant_arr = explode('****', str_replace(PHP_EOL, '', $request->get('contant')));

            if (isset($contant_arr[1]) && $contant_arr[1]) {
                $data['contant'] = '{"lang":["cn","en"],"text":["' . $contant_arr[0] . '","' . $contant_arr[1] . '"]}';
            } else {
                $data['contant'] = '{"lang":["cn"],"text":["' . $contant_arr[0] . '"]}';
            }

            if (request()->get('id')) {//修改
                DB::table('t_notice')->where('id', request()->get('id'))->update($data);
            } else {//新增
                DB::table('t_notice')->insert($data);
            }

            return back();
        }
    }

    //公告发布和删除
    public function releaseDel()
    {
        if (request()->get('option') == '1') {//发布
            DB::table('t_notice')->where('id', request()->get('id'))->update(['status' => 1]);
        } elseif (request()->get('option') == '2') {//删除
            DB::table('t_notice')->where('id', request()->get('id'))->update(['status' => 2]);
        }

        return response()->json(['status' => 200]);
    }

    //玩家基本信息查询
    public function queryBasic(Request $request)
    {
        if ($request->isMethod('post')) {

        }

        $server = DB::table('t_server_area')->get();

        return view('gm.query_basic', [
            'server' => $server
        ]);
    }

    public function queryHero(Request $request)
    {
        if ($request->isMethod('post')) {

        }

        $server = DB::table('t_server_area')->get();

        return view('gm.query_hero', [
            'server' => $server
        ]);
    }

    //货币流转记录
    public function moneyFlow(Request $request)
    {
        $data = $parameter = [];

        if ($request->isMethod('post')) {
            $request->validate([
                'beginTime' => 'required',
                'endTime' => 'required',
                'userId' => 'required',
            ]);

            $query = $this->getDoc('log_coll_asset_changed')
                ->select('pid', 'serverId', 'userId', 'roleId', 'roleName', 'itemId', 'itemNum', 'leftNum', 'logTime')
                ->where('logTime', '>=', strtotime($request->get('beginTime')) * 1000)
                ->where('logTime', '<=', strtotime($request->get('endTime')) * 1000);

            if ($request->get('pid')) {
                $query = $query->where('pid', (int)$request->get('pid'));
            }

            if ($request->get('serverId')) {
                $query = $query->where('serverId', (int)$request->get('serverId'));
            }

            $data = $query->where('userId', (int)$request->get('userId'))->get()->toArray();

            $parameter['userId'] = $request->get('userId');
            $parameter['pid'] = $request->get('pid');
            $parameter['serverId'] = $request->get('serverId');
            $parameter['beginTime'] = $request->get('beginTime');
            $parameter['endTime'] = $request->get('endTime');
        }


        $server = $this->getServer();
        $pid = $this->getPid();

        return view('gm.query_money_flow', [
            'data' => $data,
            'parameter' => $parameter,
            'server' => $server,
            'pid' => $pid
        ]);
    }

    //查询流转
    public function propertyFlow(Request $request)
    {
        $data = $parameter = [];

        if ($request->isMethod('post')) {
            $request->validate([
                'beginTime' => 'required',
                'endTime' => 'required',
                'userId' => 'required',
            ]);

            $query = $this->getDoc('log_coll_item_changed')
                ->select('pid', 'serverId', 'userId', 'roleId', 'roleName', 'itemId', 'itemNum', 'leftNum', 'logTime')
                ->where('logTime', '>=', strtotime($request->get('beginTime')) * 1000)
                ->where('logTime', '<=', strtotime($request->get('endTime')) * 1000);

            if ($request->get('pid')) {
                $query = $query->where('pid', (int)$request->get('pid'));
            }

            if ($request->get('serverId')) {
                $query = $query->where('serverId', (int)$request->get('serverId'));
            }

            $data = $query->where('userId', (int)$request->get('userId'))->get()->toArray();

            $parameter['userId'] = $request->get('userId');
            $parameter['pid'] = $request->get('pid');
            $parameter['serverId'] = $request->get('serverId');
            $parameter['beginTime'] = $request->get('beginTime');
            $parameter['endTime'] = $request->get('endTime');
        }


        $server = $this->getServer();
        $pid = $this->getPid();

        return view('gm.query_property_flow', [
            'data' => $data,
            'parameter' => $parameter,
            'server' => $server,
            'pid' => $pid
        ]);
    }
}
