@extends('common')

@include('nav.online')

@section('content')
    <section class="Hui-article-box">
        <nav class="breadcrumb"><i class="Hui-iconfont"></i> <a href="/" class="maincolor">首页</a>
            <span class="c-999 en">&gt;</span>
            <a href="/online/total" class="maincolor">在线类</a>
            <span class="c-999 en">&gt;</span>
            <span class="c-666">每日在线时段分布</span>
            <a class="btn btn-success radius r" style="line-height:1.6em;margin-top:3px"
               href="javascript:location.replace(location.href);" title="刷新"><i class="Hui-iconfont">&#xe68f;</i></a>
        </nav>
        <div class="Hui-article">
            <form action="/online/timedistribution" method="post" class="form form-horizontal">
                {!! csrf_field() !!}
                <div class="row cl">
                    <label class="form-label col-xs-1 col-sm-1">起止日期：</label>
                    <div class="formControls col-xs-1 col-sm-1">
                        <input type="text" name="interval-date-start"
                               onfocus="WdatePicker({maxDate:'#F{$dp.$D(\'datemax\')||\'%y-%M-%d\'}'})"
                               id="datemin" class="input-text Wdate"
                               placeholder="{{ date('Y-m-d',$start/1000) }}">
                    </div>
                    <div class="formControls col-xs-1 col-sm-1" style="width: 5px">-</div>
                    <div class="formControls col-xs-1 col-sm-1">
                        <input type="text" name="interval-date-end"
                               onfocus="WdatePicker({minDate:'#F{$dp.$D(\'datemin\')}',maxDate:'%y-%M-%d'})"
                               id="datemax" class="input-text Wdate"
                               placeholder="{{ date('Y-m-d',$end/1000) }}">
                    </div>
                </div>

                <div class="row cl">
                    <label class="form-label col-xs-1 col-sm-1">渠道ID：</label>
                    <div class="formControls col-xs-1 col-sm-1">
                        <span class="select-box">
                            <select class="select" size="1" name="pid">
                                <option value="0" {{ $select_pid == 0?'selected':'' }}>全部</option>
                                @foreach($pid as $v)
                                    <option value="{{ $v }}" {{ $select_pid == $v?'selected':'' }}>{{ $v }}</option>
                                @endforeach
                            </select>
				        </span>
                    </div>
                </div>

                <div class="row cl">
                    <label class="form-label col-xs-1 col-sm-1">服务器ID：</label>
                    <div class="formControls col-xs-1 col-sm-1">
                        <span class="select-box">
                            <select class="select" size="1" name="serverId">
                                <option value="0" {{ $serverId == 0?'selected':'' }}>全部</option>
                                @foreach($server as $k=>$v)
                                    <option value="{{ $k }}" {{ $serverId == $k?'selected':'' }}>{{ $v }}</option>
                                @endforeach
                            </select>
				        </span>
                    </div>
                </div>

                <div class="row cl">
                    <div class="col-xs-1 col-sm-1 col-xs-offset-1 col-sm-offset-1">
                        <button class="btn btn-success radius" type="submit">显示</button>
                    </div>
                </div>
            </form>

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
                title: {
                    text: "每日在线时段分布",
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
                        text: "人数"
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
