@extends('common')

@include('nav.income')

@section('content')
    <section class="Hui-article-box">
        <nav class="breadcrumb"><i class="Hui-iconfont"></i> <a href="/online/total" class="maincolor">在线类</a>
            <span class="c-999 en">&gt;</span>
            <span class="c-666">历史在线</span>
            <span class="c-999 en">&gt;</span>
            <span class="c-666">某个时间段充值情况</span>
            <a class="btn btn-success radius r" style="line-height:1.6em;margin-top:3px"
               href="javascript:location.replace(location.href);" title="刷新"><i class="Hui-iconfont">&#xe68f;</i></a>
        </nav>
        <div class="Hui-article">
            <article class="cl pd-20">
                <div class="text-c">
                    选择日期
                    <form action="/income/paytotal" method="post">
                        {!! csrf_field() !!}
                        <input type="text" name="start"
                               onfocus="WdatePicker({maxDate:'#F{$dp.$D(\'logmax\')||\'%y-%M-%d\'}'})"
                               id="logmin" class="input-text Wdate" style="width:120px;" placeholder="开始时间">
                        -
                        <input type="text" name="end"
                               onfocus="WdatePicker({minDate:'#F{$dp.$D(\'logmin\')}',maxDate:'%y-%M-%d'})"
                               id="logmax" class="input-text Wdate" style="width:120px;" placeholder="时间">
                        <button class="btn btn-success" type="submit"><i class="Hui-iconfont">&#xe665;</i> 查询 </button>
                    </form>
                </div>

                <div class="mt-20">
                    <table class="table table-border table-bordered table-bg table-hover table-sort">
                        <thead>
                        <tr class="text-c">
                            <th>活跃用户数</th>
                            <th>充值用户数</th>
                            <th>充值总额（元）</th>
                            <th>付费次数</th>
                            <th>付费率</th>
                            <th>付费ARPU</th>
                            <th>活跃ARPU</th>
                        </tr>
                        </thead>
                        <tbody>
                        <tr class="text-c">
                            <td>{{ $data['active'] }}</td>
                            <td>{{ $data['user_pay_sum'] }}</td>
                            <td>{{ $data['pay_money'] }}</td>
                            <td>{{ $data['pay_sum'] }}</td>
                            <td>{{ $data['pay_rat'] }}</td>
                            <td>{{ $data['pay_arpu'] }}</td>
                            <td>{{ $data['active_arpu'] }}</td>
                        </tr>
                        </tbody>
                    </table>
                </div>
            </article>
        </div>
        </div>
    </section>
@endsection