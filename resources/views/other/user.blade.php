@extends('common')
@include('nav.other')
@section('content')
    <section class="Hui-article-box">
        <nav class="breadcrumb"><i class="Hui-iconfont"></i> <a href="/" class="maincolor">首页</a>
            <span class="c-999 en">&gt;</span>
            <a href="/gm/index" class="maincolor">其他</a>
            <span class="c-999 en">&gt;</span>
            <span class="c-666">后台用户管理</span>
            <a class="btn btn-success radius r" style="line-height:1.6em;margin-top:3px"
               href="javascript:location.replace(location.href);" title="刷新"><i class="Hui-iconfont">&#xe68f;</i></a>
        </nav>

        <div class="page-container">
            <div class="cl pd-5 bg-1 bk-gray mt-20">
                <span class="l">
                    <a class="btn btn-primary radius" onclick="article_add('添加','/other/user/add','10001')"
                       href="javascript:;">
                        <i class="Hui-iconfont">&#xe600;</i> 添加管理
                    </a>
                </span>
            </div>
            <div class="mt-20">
                <table class="table table-border table-bordered table-bg table-hover table-sort table-responsive">
                    <thead>
                    <tr class="text-c">
                        <th>ID</th>
                        <th>账号</th>
                        <th>权限角色</th>
                        <th>操作</th>
                    </tr>
                    </thead>
                    <tbody>
                    @foreach($data as $v)
                        <tr>
                            <td>{{ $v->id }}</td>
                            <td>{{ $v->account }}</td>
                            <td>{{ $v->name }}</td>
                            <td>
                                <a style="text-decoration:none" class="ml-5"
                                   onClick="article_edit('编辑','/other/user/edit?id={{ $v->id }}')"
                                   href="javascript:;" title="编辑">
                                    <i class="Hui-iconfont">&#xe6df;</i>
                                </a>
                                <a style="text-decoration:none" class="ml-5"
                                   onClick="article_del(this,'/other/user/delete?id={{ $v->id }}')"
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
        $('.table-sort').dataTable();

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
        function article_del(obj, url) {
            layer.confirm('确认要删除吗？', function (index) {
                $.ajax({
                    type: 'get',
                    url: url,
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
    </script>
@endsection