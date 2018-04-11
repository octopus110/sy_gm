@extends('common')
@include('nav.gm')
@section('content')
    <section class="Hui-article-box">
        <nav class="breadcrumb"><i class="Hui-iconfont"></i> <a href="/" class="maincolor">首页</a>
            <span class="c-999 en">&gt;</span>
            <a href="/gm/index" class="maincolor">GM工具</a>
            <span class="c-999 en">&gt;</span>
            <span class="c-666">玩家信息</span>
            <a class="btn btn-success radius r" style="line-height:1.6em;margin-top:3px"
               href="javascript:location.replace(location.href);" title="刷新"><i class="Hui-iconfont">&#xe68f;</i></a>
        </nav>

        <div class="page-container">
            <div class="text-c">
                <form action="/gm/query/basic" method="post" class="form form-horizontal">
                    @csrf
                    <span class="select-box inline">
                    <select name="serverId" class="select">
                        <option value="0">选择服务器</option>
                        @foreach($server as $k=>$v)
                            <option value="{{ $k }}" {{ isset($parameter['serverId'])&&$parameter['serverId']==$k?'selected':'' }}>{{ $v }}</option>
                        @endforeach
                    </select>
		        </span>
                    <input type="text" name="roleId"
                           placeholder="{{ isset($parameter['roleId'])?$parameter['roleId']:'角色ID' }}"
                           style="width:250px" class="input-text"/>
                    <input type="text" name="roleNick"
                           placeholder="{{ isset($parameter['roleNick'])?$parameter['roleNick']:'角色昵称' }}"
                           style="width:250px" class="input-text"/>
                    <button name="" id="" class="btn btn-success" type="submit"><i class="Hui-iconfont">&#xe665;</i> 查询
                    </button>
                </form>
            </div>
            <div class="mt-20">
                <table class="table table-border table-bordered table-bg table-hover table-sort table-responsive">
                    <thead>
                    <tr class="text-c">
                        <th>服务器</th>
                        <th>渠道</th>
                        <th>玩家ID</th>
                        <th>角色ID</th>
                        <th>角色昵称</th>
                        <th>最近一次登陆时等级</th>
                        <th>更多信息</th>
                        <th>记录</th>
                        <th>操作</th>
                    </tr>
                    </thead>
                    <tbody>
                    @foreach($data as $v)
                        <tr>
                            <td>{{ $v['serverName'] }}</td>
                            <td>{{ $v['pidText'] }}</td>
                            <td>{{ $v['userId'] }}</td>
                            <td>{{ $v['roleId'] }}</td>
                            <td>{{ $v['roleName'] }}</td>
                            <td>{{ $v['roleLevel'] }}</td>
                            <td>
                                <a onclick="get_more('更多信息','/gm/query/basic/more?serverId={{ $v['serverId'] }}&roleId={{ $v['roleId'] }}&roleName={{ $v['roleName'] }}','10001')"
                                   href="javascript:;" class="btn btn-info radius">查看更多</a>
                            </td>
                            <td>
                                <a onclick=article_add('查看充值记录','/gm/query/recharge?id={{ $v['roleId'] }}','10001')
                                   href="javascript:;" class="btn btn-info radius">充值记录</a>
                            </td>
                            <td>
                                <a onClick=get_more('禁言','/gm/query/shutup?roleIds={{ $v["roleId"] }}&serverId={{ $v["serverId"] }}')
                                   href="javascript:;" class="btn btn-danger radius">禁言</a>
                                <a onClick=get_more('账号解冻','/gm/query/lock?userId={{ $v["userId"] }}&roleName={{ $v["roleName"] }}')
                                   href="javascript:;" class="btn btn-danger radius">账号解冻</a>
                                <a onClick=get_more('角色解冻','/gm/query/lock/role?serverId={{ $v["serverId"] }}&roleId={{ $v["roleId"] }}')
                                   href="javascript:;" class="btn btn-danger radius">角色解冻</a>
                                <a onClick=kick(this,'/gm/query/kick?serverId={{ $v["serverId"] }}&userId={{ $v["userId"] }}')
                                   href="javascript:;" class="btn btn-danger radius">踢下线</a>
                                <a onClick=get_more('充值补单','/gm/query/pay?serverId={{ $v["serverId"] }}&roleId={{ $v["roleId"] }}&pid={{ $v['pid'] }}&passportId={{$v['userId']}}')
                                   href="javascript:;" class="btn btn-info radius">充值补单</a>
                            </td>
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
            "pading": false,
            "aoColumnDefs": [
                {"orderable": false, "aTargets": [0, 6]}// 不参与排序的列
            ]
        });

        /*添加*/
        function article_add(title, url, w, h) {
            var index = layer.open({
                type: 2,
                title: title,
                content: url
            });
            layer.full(index);
        }

        /*获取更多信息*/
        function get_more(title, url, w, h) {
            var index = layer.open({
                type: 2,
                title: title,
                content: url
            });
            layer.full(index);
        }

        /*踢下线*/
        function kick(obj, url) {
            layer.confirm('确认踢下线吗？', function (index) {
                $.ajax({
                    type: 'get',
                    url: url,
                    dataType: 'json',
                    success: function (data) {
                        if (data.status) {
                            layer.msg('操作成功', {icon: 1, time: 1000});
                        }
                    },
                    error: function (data) {
                        console.log(data.msg);
                    },
                });
            });
        }
    </script>
@endsection