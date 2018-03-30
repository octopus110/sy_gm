@extends('common')

@include('nav.user')

@section('content')
    <section class="Hui-article-box">
        <nav class="breadcrumb"><i class="Hui-iconfont"></i> <a href="/" class="maincolor">首页</a>
            <span class="c-999 en">&gt;</span>
            <a href="/income/paytotal" class="maincolor">用户类</a>
            <span class="c-999 en">&gt;</span>
            <span class="c-666">实时充值总况</span>
            <a class="btn btn-success radius r" style="line-height:1.6em;margin-top:3px"
               href="javascript:location.replace(location.href);" title="刷新"><i class="Hui-iconfont">&#xe68f;</i></a>
        </nav>

        <div class="Hui-article">
            <form action="/user/total" method="post" class="form form-horizontal">
                {!! csrf_field() !!}

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
                        <button class="btn btn-success radius" type="submit"><i class="Hui-iconfont">&#xe665;</i>查询
                        </button>
                    </div>

                    <div class="col-xs-1 col-sm-1">
                        <a href="/user/total?status=2" target="_blank" class="btn btn-secondary radius">导出</a>
                    </div>
                    <div class="col-xs-2 col-sm-2">
                        <a href="javascript:;" onclick="show_graph('最高在线/平均在线','/user/total?status=3')"
                           class="btn btn-secondary radius">图形化显示(新增用户)</a>
                    </div>
                    <div class="col-xs-2 col-sm-2">
                        <a href="javascript:;" onclick="show_graph('最高在线/平均在线','/user/total?status=4')"
                           class="btn btn-secondary radius">图形化显示(活跃用户)</a>
                    </div>
                    <div class="col-xs-2 col-sm-2">
                        <a href="javascript:;" onclick="show_graph('最高在线/平均在线','/user/total?status=5')"
                           class="btn btn-secondary radius">图形化显示(人均登陆次数)</a>
                    </div>
                    <div class="col-xs-2 col-sm-2">
                        <a href="javascript:;" onclick="show_graph('最高在线/平均在线','/user/total?status=6')"
                           class="btn btn-secondary radius">图形化显示(人均登陆次数幅度)</a>
                    </div>
                </div>
            </form>

            <div class="mt-20 col-xs-12 col-sm-12">
                <table class="table table-border table-bordered table-bg table-hover table-sort">
                    <thead>
                    <tr class="text-c">
                        <th>日期</th>
                        <th>新增用户</th>
                        <th>活跃用户</th>
                        <th>登录次数</th>
                        <th>人均登录次数</th>
                        <th>人均登录次数增幅</th>

                    </tr>
                    </thead>
                    <tbody>

                    @foreach($data as $k=>$v)
                        <tr class="text-c">
                            <td>{{ $k }}</td>
                            <td>{{ $v['new_user'] }}</td>
                            <td>{{ $v['active'] }}</td>
                            <td>{{ $v['login_sum'] }}</td>
                            <td>{{ $v['login_avg_sum'] }}</td>
                            <td>{{ $v['login_avg_rat'] }}</td>
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