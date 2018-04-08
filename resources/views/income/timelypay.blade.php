@extends('common')

@include('nav.income')

@section('content')
    <section class="Hui-article-box">
        <nav class="breadcrumb"><i class="Hui-iconfont"></i> <a href="/" class="maincolor">首页</a>
            <span class="c-999 en">&gt;</span>
            <a href="/income/paytotal" class="maincolor">收入类</a>
            <span class="c-999 en">&gt;</span>
            <span class="c-666">实时充值情况</span>
            <a class="btn btn-success radius r" style="line-height:1.6em;margin-top:3px"
               href="javascript:location.replace(location.href);" title="刷新"><i class="Hui-iconfont">&#xe68f;</i></a>
        </nav>
        <div class="Hui-article">
            <form action="/income/timelypay" method="post" class="form form-horizontal">
                {!! csrf_field() !!}

                <div class="row cl">
                    <label class="form-label col-xs-1 col-sm-1">渠道ID：</label>
                    <div class="formControls col-xs-1 col-sm-1">
                        <span class="select-box">
                            <select class="select" size="1" name="pid">
                                <option value="0" {{ $select_pid == 0?'selected':'' }}>全部</option>
                                @foreach($pid as $k=>$v)
                                    <option value="{{ $k }}" {{ $select_pid == $k?'selected':'' }}>{{ $v }}</option>
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
                        <button class="btn btn-success radius" type="submit">
                            <i class="Hui-iconfont">&#xe665;</i>查询
                        </button>
                    </div>
                    <div class="col-xs-1 col-sm-1">
                        <a href="/income/timelypay?status=2" target="_blank" class="btn btn-secondary radius">导出</a>
                    </div>
                    <div class="col-xs-2 col-sm-2">
                        <a href="javascript:;" onclick="show_graph('充值总况','/income/paytotal?status=3')"
                           class="btn btn-secondary radius">图形化显示(充值用户数)</a>
                    </div>

                    <div class="col-xs-2 col-sm-2">
                        <a href="javascript:;" onclick="show_graph('充值总况','/income/paytotal?status=4')"
                           class="btn btn-secondary radius">图形化显示(付费率)</a>
                    </div>

                    <div class="col-xs-2 col-sm-2">
                        <a href="javascript:;" onclick="show_graph('充值总况','/income/paytotal?status=5')"
                           class="btn btn-secondary radius">图形化显示(ARPU)</a>
                    </div>
                </div>
            </form>

            <div class="mt-20 col-xs-12 col-sm-12">
                <table class="table table-border table-bordered table-bg table-hover table-sort">
                    <thead>
                    <tr class="text-c">
                        <th>时间（小时）</th>
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
                    @foreach($data as $k=>$v)
                        <tr class="text-c">
                            <td>{{ $k }}</td>
                            <td>{{ $v['active'] }}</td>
                            <td>{{ $v['user_pay_sum'] }}</td>
                            <td>{{ $v['pay_money'] }}</td>
                            <td>{{ $v['pay_sum'] }}</td>
                            <td>{{ $v['pay_rat'] }}</td>
                            <td>{{ $v['pay_arpu'] }}</td>
                            <td>{{ $v['active_arpu'] }}</td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </section>
@endsection

@section('js')
    <script type="text/javascript">
        $('.table-sort').dataTable({
            "aaSorting": [[0, "desc"]],//默认第几个排序
            "bStateSave": true,//状态保存
        });
    </script>
@endsection