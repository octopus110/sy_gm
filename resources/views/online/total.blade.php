@extends('common')

@include('nav.online')

@section('content')
    <section class="Hui-article-box">
        <nav class="breadcrumb"><i class="Hui-iconfont"></i>
            <a href="/" class="maincolor">首页</a>
            <span class="c-999 en">&gt;</span>
            <a href="/online/total" class="maincolor">在线类</a>
            <span class="c-999 en">&gt;</span>
            <span class="c-666">总实时在线</span>
            <a class="btn btn-success radius r" style="line-height:1.6em;margin-top:3px"
               href="javascript:location.replace(location.href);" title="刷新"><i class="Hui-iconfont">&#xe68f;</i></a>
        </nav>
        <div class="Hui-article">
            <article class="cl pd-20">
                <div id="container" style="min-width:700px;height:400px"></div>
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
                chart: {
                    type: 'column'
                },
                title: {
                    text: '实时服务器状态'
                },
                xAxis: {
                    title: {
                        text: '服务器ID'
                    },
                    categories: [{!! $data['x'] !!}]
                },
                yAxis: {
                    min: 0,
                    title: {
                        text: '容量'
                    }
                },
                tooltip: {
                    headerFormat: '<span style="font-size:10px">服务器ID：{point.key}</span><br/>',
                    pointFormat: '<tr><td style="color:{series.color};padding:0">{series.name}: </td>' +
                    '<td style="padding:0"><b>{point.y:.1f}</b></td></tr>',
                    shared: true,
                    useHTML: true
                },
                plotOptions: {
                    column: {
                        pointPadding: 0.2,
                        borderWidth: 0
                    }
                },
                series: [{
                    name: '在线人数',
                    data: [{!! $data['y1'] !!}]

                }, {
                    name: '剩余容量',
                    data: [{!! $data['y2'] !!}]

                }]
            });
        });
    </script>
@endsection