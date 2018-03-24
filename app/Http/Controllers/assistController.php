<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Storage;
use Maatwebsite\Excel\Facades\Excel;

class assistController extends Controller
{
    //生成excel表格
    public static function exportExcel($title, $cellData)
    {
        Excel::create($title, function ($excel) use ($cellData) {
            $excel->sheet('GMexcel', function ($sheet) use ($cellData) {
                $sheet->rows($cellData);
            });
        })->export('xls');

    }

    //保存数据到文件
    public static function dataFile($oper, $fileName, $data = [])
    {
        switch ($oper) {
            case 'save':
                $rst = Storage::disk('local')->put($fileName, json_encode($data));
                break;
            case 'read':
                $rst = json_decode(Storage::disk('local')->get($fileName), true);
                break;
        }
        return $rst;
    }
}
