@extends('common')

@include('nav.online')

@section('content')
    <section class="Hui-article-box">
        <nav class="breadcrumb"><i class="Hui-iconfont"></i> <a href="/online/total" class="maincolor">在线类</a>
            <span class="c-999 en">&gt;</span>
            <span class="c-666">历史在线</span>
            <span class="c-999 en">&gt;</span>
            <span class="c-666">DAU和登录次数</span>
            <a class="btn btn-success radius r" style="line-height:1.6em;margin-top:3px"
               href="javascript:location.replace(location.href);" title="刷新"><i class="Hui-iconfont">&#xe68f;</i></a>
        </nav>
        <div class="Hui-article">
            <article class="cl pd-20">
                <div class="text-c">
                    选择日期
                    <form action="/online/dau" method="post">
                        {!! csrf_field() !!}
                        <input type="text" name="start"
                               onfocus="WdatePicker({maxDate:'#F{$dp.$D(\'logmax\')||\'%y-%M-%d\'}'})"
                               id="logmin" class="input-text Wdate" style="width:120px;" placeholder="开始时间">
                        -
                        <input type="text" name="end"
                               onfocus="WdatePicker({minDate:'#F{$dp.$D(\'logmin\')}',maxDate:'%y-%M-%d'})"
                               id="logmax" class="input-text Wdate" style="width:120px;" placeholder="结束时间">
                        <button class="btn btn-success" type="submit"><i class="Hui-iconfont">&#xe665;</i> 查询
                        </button>
                    </form>
                </div>

                <div class="mt-20">
                    <table class="table table-border table-bordered table-bg table-hover table-sort">
                        <thead>
                        <tr class="text-c">
                            <th>统计时间</th>
                            <th>当天活跃用户</th>
                            <th>用户登陆次数</th>
                            <th>人均登陆次数</th>
                        </tr>
                        </thead>
                        <tbody>
                        <tr class="text-c">
                            <td>{{ $data['time']['start'] .' 到 '.$data['time']['end'] }}</td>
                            <td>{{ $data['active'] }}</td>
                            <td>{{ $data['login_sum'] }}</td>
                            <td>{{ $data['login_avg'] }}</td>
                        </tr>
                        </tbody>
                    </table>
                </div>
            </article>
        </div>
        </div>
    </section>
@endsection