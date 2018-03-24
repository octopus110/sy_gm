@extends('common')

@include('nav.online')

@section('content')
    <section class="Hui-article-box">
        <nav class="breadcrumb"><i class="Hui-iconfont"></i> <a href="/online/total" class="maincolor">在线类</a>
            <span class="c-999 en">&gt;</span>
            <span class="c-666">历史在线</span>
            <span class="c-999 en">&gt;</span>
            <span class="c-666">平均单次在线时长</span>
            <a class="btn btn-success radius r" style="line-height:1.6em;margin-top:3px"
               href="javascript:location.replace(location.href);" title="刷新"><i class="Hui-iconfont">&#xe68f;</i></a>
        </nav>
        <div class="Hui-article">
            <article class="cl pd-20">
                <div class="text-c">
                    选择日期
                    <form action="/online/frequency" method="post">
                        {!! csrf_field() !!}
                        <input type="text" name="start"
                               onfocus="WdatePicker({maxDate:'#F{$dp.$D(\'logmax\')||\'%y-%M-%d\'}'})"
                               id="logmin" class="input-text Wdate" style="width:120px;">
                        <input type="text" name="end"
                               onfocus="WdatePicker({minDate:'#F{$dp.$D(\'logmin\')}',maxDate:'%y-%M-%d'})"
                               id="logmax" class="input-text Wdate" style="width:120px;">
                        <button class="btn btn-success" type="submit"><i class="Hui-iconfont">&#xe665;</i> 查询
                        </button>
                    </form>
                </div>

                <div class="mt-20">
                    <p>查询区间：{{ date('Y/m/d H:i:s',$start/1000).' - '.date('Y/m/d H:i:s',$end/1000) }}</p>
                    <table class="table table-border table-bordered table-bg table-hover table-sort">
                        <thead>
                        <tr class="text-c">
                            <th>在线总时长</th>
                            <th>在线次数</th>
                            <th>平均单次在线时长</th>
                        </tr>
                        </thead>
                        <tbody>
                        <tr class="text-c">
                            <td>{{ round($data['mistiming']/60000,3) }}</td>
                            <td>{{ $data['time'] }}</td>
                            <td>{{ $data['time']==0?0:round($data['mistiming']/$data['time']/60000,3) }}</td>
                        </tr>
                        </tbody>
                    </table>
                </div>
            </article>
        </div>
        </div>
    </section>
@endsection