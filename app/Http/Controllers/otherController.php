<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use DB;
use QrCode;
use Illuminate\Support\Facades\Storage;

class otherController extends Controller
{
    public function user()
    {
        $data = DB::table('t_user')
            ->select('t_user.id', 't_user.account', 't_group.name')
            ->leftjoin('t_user_group', 't_user_group.userId', 't_user.id')
            ->leftjoin('t_group', 't_group.id', 't_user_group.groupId')
            ->get();

        return view('other.user', ['data' => $data]);
    }

    public function userAdd(Request $request)
    {
        $msg = '';
        if ($request->isMethod('post')) {
            $request->validate([
                'account' => 'required',
                'password' => 'required',
                'position' => 'required',
            ]);

            DB::beginTransaction();
            try {
                $user_id = DB::table('t_user')->insertGetId([
                    'account' => $request->get('account'),
                    'password' => $request->get('password'),
                    'createTime' => date('Y-m-d H:i:s', time())
                ]);
                if (!$user_id) {
                    throw new \Exception("用户插入失败");
                }

                $res = DB::table('t_user_group')->insert([
                    'userId' => $user_id,
                    'groupId' => $request->get('position')
                ]);
                if (!$res) {
                    throw new \Exception("用户权限信息插入失败");
                }

                $msg = '数据保存成功';

                DB::commit();
            } catch (\Exception $e) {
                DB::rollback();//事务回滚
                echo $e->getMessage();
                echo $e->getCode();
            }
        }

        //获取权限列表
        $data = DB::table('t_group')
            ->select('id', 'name')
            ->get();

        return view('other.user_add', [
            'data' => $data,
            'msg' => $msg
        ]);
    }

    public function userEdit(Request $request)
    {
        $id = $request->get('id');
        $msg = '';

        if ($request->isMethod('post')) {
            $request->validate([
                'account' => 'required',
                'password' => 'required',
                'position' => 'required',
            ]);

            DB::beginTransaction();
            try {
                DB::table('t_user')
                    ->where('id', $id)
                    ->update([
                        'account' => $request->get('account'),
                        'password' => $request->get('password')
                    ]);

                DB::table('t_user_group')
                    ->where('userId', $id)
                    ->update([
                        'groupId' => $request->get('position')
                    ]);

                $msg = '数据保存成功';

                DB::commit();
            } catch (\Exception $e) {
                DB::rollback();//事务回滚
                echo $e->getMessage();
                echo $e->getCode();
            }

            $msg = '数据修改成功';
        }
        $user = DB::table('t_user')
            ->select('t_user.id', 't_user.account', 't_user.password', 't_user_group.groupId')
            ->Join('t_user_group', 't_user_group.userId', 't_user.id')
            ->where('t_user.id', $id)
            ->first();

        //获取权限列表
        $data = DB::table('t_group')
            ->select('id', 'name')
            ->get();

        return view('other.user_edit', [
            'data' => $data,
            'user' => $user,
            'msg' => $msg
        ]);
    }

    public function userDelete()
    {
        $id = request()->get('id');
        DB::table('t_user')->where('id', $id)->delete();

        return response()->json([]);
    }

    public function record()
    {
        //获取当前所有管理员
        $user = DB::table('t_user')->select('account')->get();

        //查询记录
        $record_db = DB::table('t_user_record');
        if (request()->get('interval-date-start')) {
            $record_db = $record_db->where('recordTime', '>=', request()->get('interval-date-start'));
        }
        if (request()->get('interval-date-end')) {
            $record_db = $record_db->where('recordTime', '<=', request()->get('interval-date-end'));
        }
        if (request()->get('account')) {
            $record_db = $record_db->where('userName', request()->get('account'));
        }
        $data = $record_db->limit(100)->get();

        return view('other.record', [
            'data' => $data,
            'user' => $user,
            'time' => [
                'start' => request()->get('interval-date-start'),
                'end' => request()->get('interval-date-end')
            ]
        ]);
    }

    //生成二维码
    public function QRcode(Request $request)
    {
        if ($request->isMethod('get')) {
            $scan_times = json_decode(Storage::disk('local')->get('scan_times'), true);
            if (!$scan_times) {
                $scan_times = [
                    'android' => 0,
                    'ios' => 0,
                    'sum' => 0
                ];

                Storage::disk('local')->put('scan_times', json_encode($scan_times));
            }

            return view('other.QRcode', [
                'data' => $scan_times,
            ]);
        } else {
            Storage::disk('local')->put('QR_url', json_encode([
                'android' => $request->get('android'),
                'ios' => $request->get('ios')
            ]));

            $QR_arr = [];
            $size_arr = $request->get('size');
            foreach ($size_arr as $v) {
                switch ($v) {
                    case 8:
                        $QR_path = public_path('qr') . DIRECTORY_SEPARATOR . 'qrcode_8.png';
                        QrCode::format('png')->size(226)->generate(asset('other/scan'), $QR_path);
                        $QR_arr[] = [
                            '8cm', '/qr/qrcode_8.png'
                        ];
                        break;
                    case 12:
                        $QR_path = public_path('qr') . DIRECTORY_SEPARATOR . 'qrcode_12.png';
                        QrCode::format('png')->size(340)->generate(asset('other/scan'), $QR_path);
                        $QR_arr[] = [
                            '12cm', '/qr/qrcode_12.png'
                        ];
                        break;
                    case 15:
                        $QR_path = public_path('qr') . DIRECTORY_SEPARATOR . 'qrcode_15.png';
                        QrCode::format('png')->size(425)->generate(asset('other/scan'), $QR_path);
                        $QR_arr[] = [
                            '15cm', '/qr/qrcode_15.png'
                        ];
                        break;
                    case 30:
                        $QR_path = public_path('qr') . DIRECTORY_SEPARATOR . 'qrcode_30.png';
                        QrCode::format('png')->size(850)->generate(asset('other/scan'), $QR_path);
                        $QR_arr[] = [
                            '30cm', '/qr/qrcode_30.png'
                        ];
                        break;
                }
            }
            return response()->json([
                'status' => 1,
                'msg' => 'success',
                'data' => $QR_arr,
            ]);
        }
    }

    //扫描二维码
    public function scan()
    {
        //统计扫描次数
        $scan_times = json_decode(Storage::disk('local')->get('scan_times'), true);
        $android = $scan_times['android'];
        $ios = $scan_times['ios'];
        $sum = $android + $ios;

        $QR_url = json_decode(Storage::disk('local')->get('QR_url'), true);

        if ($this->get_device_type()) {
            Storage::disk('local')->put('scan_times', json_encode([
                'android' => $android + 1,
                'ios' => $ios,
                'sum' => $sum + 1
            ]));
            return redirect($QR_url['android']);
        } else {
            Storage::disk('local')->put('scan_times', json_encode([
                'android' => $android,
                'ios' => $ios + 1,
                'sum' => $sum + 1
            ]));
            return redirect($QR_url['ios']);
        }
    }

    //判断设备类型
    private function get_device_type()
    {
        //全部变成小写字母
        $agent = strtolower($_SERVER['HTTP_USER_AGENT']);

        if (strpos($agent, 'android')) {
            return true;
        }
        return false;
    }
}
