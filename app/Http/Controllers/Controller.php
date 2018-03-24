<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use DB;

class Controller extends BaseController
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;

    protected function getDoc($obj)
    {
        return DB::collection($obj);
    }

    //获取route路径
    public function getPath()
    {
        return explode('/', request()->path());
    }

    //获取时间
    public function getTime($day = 1)
    {

        if (request()->start) {
            $start = strtotime(request()->start) * 1000;
        } else {
            $start = time() * 1000 - 86400000 * $day;//day天的秒数
        }

        if (request()->end) {
            $end = strtotime(request()->end) * 1000;
        } else {
            $end = time() * 1000;
        }

        return [$start, $end];
    }

    //按时间重新组合数组
    public function rearrayToTime($data, $time)
    {
        $result = [];
        foreach ($data as $v) {
            $result[date('Y-m-d', $v[$time] / 1000)][] = $v;
        }

        return $result;
    }
}
