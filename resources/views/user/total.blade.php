@extends('common')

@include('nav.user')

@section('content')
    <section class="Hui-article-box">
        <nav class="breadcrumb"><i class="Hui-iconfont"></i> <a href="/online/total" class="maincolor">用户类</a>
            <span class="c-999 en">&gt;</span>
            <span class="c-666">某时间段用户总况</span>
            <a class="btn btn-success radius r" style="line-height:1.6em;margin-top:3px"
               href="javascript:location.replace(location.href);" title="刷新"><i class="Hui-iconfont">&#xe68f;</i></a>
        </nav>
        <div class="Hui-article">
            <article class="cl pd-20">
                <div class="text-c">
                    选择日期
                    <form action="/user/total" method="post">
                        {!! csrf_field() !!}
                        <input type="text" name="start"
                               onfocus="WdatePicker({maxDate:'#F{$dp.$D(\'logmax\')||\'%y-%M-%d\'}'})"
                               id="logmin" class="input-text Wdate" style="width:120px;" placeholder="开始时间">

                        <input type="text" name="end"
                               onfocus="WdatePicker({minDate:'#F{$dp.$D(\'logmin\')}',maxDate:'%y-%M-%d'})"
                               id="logmax" class="input-text Wdate" style="width:120px;" placeholder="时间">
                        <button class="btn btn-success" type="submit"><i class="Hui-iconfont">&#xe665;</i> 查询</button>
                    </form>
                </div>

                <div class="mt-20">
                    <table class="table table-border table-bordered table-bg table-hover table-sort">
                        <thead>
                        <tr class="text-c">
                            <th>新增用户</th>
                            <th>活跃用户</th>
                            <th>下载量</th>
                            <th>登录次数</th>
                            <th>人均登录次数</th>
                            <th>人均登录次数增幅</th>

                        </tr>
                        </thead>
                        <tbody>
                        <tr class="text-c">
                            <td>{{ $data['new_user'] }}</td>
                            <td>{{ $data['active'] }}</td>
                            <td>{{ $data['downloads'] }}</td>
                            <td>{{ $data['login_sum'] }}</td>
                            <td>{{ $data['login_avg_sum'] }}</td>
                            <td>{{ $data['login_avg_rat'] }}</td>
                        </tr>
                        </tbody>
                    </table>
                </div>
            </article>
        </div>
    </section>
@endsection