@extends('common')
@include('nav.gm')
@section('content')
    <section class="Hui-article-box">
        <nav class="breadcrumb"><i class="Hui-iconfont"></i> <a href="/" class="maincolor">首页</a>
            <span class="c-999 en">&gt;</span>
            <a href="/gm/index" class="maincolor">GM工具</a>
            <span class="c-999 en">&gt;</span>
            <span class="c-666">首页</span>
            <a class="btn btn-success radius r" style="line-height:1.6em;margin-top:3px"
               href="javascript:location.replace(location.href);" title="刷新"><i class="Hui-iconfont">&#xe68f;</i></a>
        </nav>

        <div class="page-container">
            <div class="cl pd-5 bg-1 bk-gray mt-20">
                <span class="l">
                    <a class="btn btn-primary radius" onclick="article_add('添加','/gm/notice/new_edit','10001')"
                       href="javascript:;">
                        <i class="Hui-iconfont">&#xe600;</i> 添加公告
                    </a>
                </span>
                <span class="r">共有数据：<strong>{{ count($data) }}</strong> 条</span>
            </div>
            <div class="mt-20">
                <table class="table table-border table-bordered table-bg table-hover table-sort table-responsive">
                    <thead>
                    <tr class="text-c">
                        <th>ID</th>
                        <th>标题</th>
                        <th>内容</th>
                        <th>开始时间</th>
                        <th>结束时间</th>
                        <th>排序</th>
                        <th>发布状态</th>
                        <th>操作</th>
                    </tr>
                    </thead>
                    <tbody>
                    @foreach($data as $v)
                        <tr class="text-c">
                            <td>
                                {{ $v->id }}
                            </td>
                            <td class="text-l">
                                <u style="cursor:pointer" class="text-primary"
                                   onClick="article_edit('查看','/gm/notice/new_edit?id={{ $v->id }}','10001')"
                                   title="查看">
                                    {{ $v->title[$v->language] }}
                                </u>
                            </td>
                            <td>{{ substr($v->contant[$v->language],0,60) }}...</td>
                            <td>{{ $v->beginTime }}</td>
                            <td>{{ $v->endTime }}</td>
                            <td>{{ $v->weight }}</td>
                            <td class="td-status">
                                @if($v->status == 1)
                                    <span class="label label-success radius">已发布</span>
                                @else
                                    <span class="label radius">草稿</span>
                                @endif
                            </td>
                            <td class="f-14 td-manage">
                                @if($v->status != 1)
                                    <a style="text-decoration:none" onClick="article_stop(this,{{ $v->id }})"
                                       href="javascript:;" title="发布">
                                        <i class="Hui-iconfont">&#xe603;</i>
                                    </a>
                                @endif
                                <a style="text-decoration:none" class="ml-5"
                                   onClick="article_edit('编辑','/gm/notice/new_edit?id={{ $v->id }}')"
                                   href="javascript:;" title="编辑">
                                    <i class="Hui-iconfont">&#xe6df;</i>
                                </a>
                                <a style="text-decoration:none" class="ml-5" onClick="article_del(this,{{ $v->id }})"
                                   href="javascript:;" title="删除">
                                    <i class="Hui-iconfont">&#xe6e2;</i>
                                </a>
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

        /*编辑*/
        function article_edit(title, url, id, w, h) {
            var index = layer.open({
                type: 2,
                title: title,
                content: url
            });
            layer.full(index);
        }

        /*删除*/
        function article_del(obj, id) {
            layer.confirm('确认要删除吗？', function (index) {
                $.ajax({
                    type: 'get',
                    url: '/gm/notice/release_del?option=2&id=' + id,
                    dataType: 'json',
                    success: function (data) {
                        layer.msg('已删除!', {icon: 1, time: 1000});
                        location.reload();
                    },
                    error: function (data) {
                        console.log(data.msg);
                    },
                });
            });
        }

        /*发布*/
        function article_stop(obj, id) {
            layer.confirm('确认要发布吗？', function (index) {
                $.ajax({
                    type: 'get',
                    url: '/gm/notice/release_del?option=1&id=' + id,
                    dataType: 'json',
                    success: function (data) {
                        layer.msg('已发布!', {icon: 1, time: 1000});
                        location.reload();
                    },
                    error: function (data) {
                        console.log(data.msg);
                    },
                });
            });
        }
    </script>
@endsection