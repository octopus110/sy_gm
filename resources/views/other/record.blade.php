@extends('common')
@include('nav.other')
@section('content')
    <section class="Hui-article-box">
        <nav class="breadcrumb"><i class="Hui-iconfont"></i> <a href="/" class="maincolor">首页</a>
            <span class="c-999 en">&gt;</span>
            <a href="/gm/index" class="maincolor">GM工具</a>
            <span class="c-999 en">&gt;</span>
            <span class="c-666">操作记录</span>
            <a class="btn btn-success radius r" style="line-height:1.6em;margin-top:3px"
               href="javascript:location.replace(location.href);" title="刷新"><i class="Hui-iconfont">&#xe68f;</i></a>
        </nav>

        <div class="page-container">
            <form action="/other/user/record" method="post" class="form form-horizontal">
                {!! csrf_field() !!}
                <div class="row cl">
                    <label class="form-label col-xs-1 col-sm-1">起止日期：</label>
                    <div class="formControls col-xs-1 col-sm-1">
                        <input type="text" name="interval-date-start"
                               onfocus="WdatePicker({maxDate:'#F{$dp.$D(\'datemax\')||\'%y-%M-%d\'}'})"
                               id="datemin" class="input-text Wdate"
                               placeholder="{{ $time['start'] }}">
                    </div>
                    <div class="formControls col-xs-1 col-sm-1" style="width: 5px">-</div>
                    <div class="formControls col-xs-1 col-sm-1">
                        <input type="text" name="interval-date-end"
                               onfocus="WdatePicker({minDate:'#F{$dp.$D(\'datemin\')}',maxDate:'%y-%M-%d'})"
                               id="datemax" class="input-text Wdate"
                               placeholder="{{ $time['end'] }}">
                    </div>
                </div>

                <div class="row cl">
                    <label class="form-label col-xs-1 col-sm-1">选择账号：</label>
                    <div class="formControls col-xs-1 col-sm-1">
                        <span class="select-box">
                            <select class="select" size="1" name="account">
                                <option value="0">全部</option>
                                @foreach($user as $v)
                                    <option value="{{ $v->account }}">{{ $v->account }}</option>
                                @endforeach
                            </select>
				        </span>
                    </div>
                </div>

                <div class="row cl">
                    <label class="form-label col-xs-1 col-sm-1">操作类型：</label>
                    <div class="formControls col-xs-1 col-sm-1">
                        <span class="select-box">
                            <select class="select" size="1" name="operation">
                                <option value="0">全部</option>
                                <option value="用户登录">用户登录</option>
                                <option value="道具">道具操作</option>
                                <option value="禁言">禁言封号</option>
                                <option value="公告">公告日志</option>
                                <option value="补单">补单日志</option>
                                <option value="关卡">关卡、引导日志</option>
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
                </div>
            </form>

            <div class="mt-20">
                <table class="table table-border table-bordered table-bg table-hover table-sort table-responsive">
                    <thead>
                    <tr class="text-c">
                        <th>管理员账号</th>
                        <th>操作记录</th>
                        <th>操作数据</th>
                        <th>操作时间</th>
                    </tr>
                    </thead>
                    <tbody>
                    @foreach($data as $v)
                        <tr>
                            <td>{{ $v->userName }}</td>
                            <td>{{ $v->recordDesc }}</td>
                            <td>{{ $v->recordData }}</td>
                            <td width="120">{{ $v->recordTime }}</td>
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
            "aaSorting": [[3, "desc"]],//默认第几个排序
        });
    </script>
@endsection