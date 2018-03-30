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
        $notice = DB::table('t_notice')->get()->toArray();

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
            } else {
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

            
        }
    }
}
