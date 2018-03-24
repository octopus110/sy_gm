@extends('common')

@include('nav.online')

@section('content')
    <section class="Hui-article-box">
        <nav class="breadcrumb"><i class="Hui-iconfont"></i> <a href="/online/total" class="maincolor">在线类</a>
            <span class="c-999 en">&gt;</span>
            <span class="c-666">历史在线</span>
            <span class="c-999 en">&gt;</span>
            <span class="c-666">每日在线时段分布</span>
            <a class="btn btn-success radius r" style="line-height:1.6em;margin-top:3px"
               href="javascript:location.replace(location.href);" title="刷新"><i class="Hui-iconfont">&#xe68f;</i></a>
        </nav>
        <div class="Hui-article">
            <article class="cl pd-20">
                <div class="text-c">
                    选择日期
                    <form action="/online/timedistribution" method="post">
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

                    <article class="cl pd-20">
                        <p>查询区间：{{ date('Y/m/d H:i:s',$start/1000).' - '.date('Y/m/d H:i:s',$end/1000) }}</p>
                        <div id="container" style="min-width:700px;height:400px"></div>
                    </article>
                </div>
            </article>
        </div>
    </section>
@endsection

@section('js')
    <script type="text/javascript" src="/lib/hcharts/Highcharts/5.0.6/js/highcharts.js"></script>
    <script type="text/javascript" src="/lib/hcharts/Highcharts/5.0.6/js/modules/exporting.js"></script>
    <script type="text/javascript">
        $(function () {
            $('#container').highcharts({
                title: {
                    text: '每日在线时段分布',
                    x: -20 //center
                },
                xAxis: {
                    title: {
                        text: '时间'
                    },
                    categories: [{!! $data['x'] !!}]
                },
                yAxis: {
                    title: {
                        text: '人数'
                    },
                    plotLines: [{
                        value: 0,
                        width: 1,
                        color: '#808080'
                    }]
                },
                legend: {
                    layout: 'vertical',
                    align: 'right',
                    verticalAlign: 'middle',
                    borderWidth: 0
                },
                series: [{
                    name: '人数',
                    data: [{{ $data['y'] }}]
                }]
            });
        });
    </script>
@endsection
