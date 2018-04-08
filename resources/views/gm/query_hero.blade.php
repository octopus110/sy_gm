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
            <div class="text-c">
                <span class="select-box inline">
                    <select name="" class="select">
                        <option value="0">选择大区</option>
                        @foreach($server as $v)
                            <option value="{{ $v->id }}">{{ $v->name }}</option>
                        @endforeach
                    </select>
		        </span>
                <span class="select-box inline">
                    <select name="" class="select">
                        <option value="0">选择服务器</option>
                        @foreach($server as $v)
                            <option value="{{ $v->id }}">{{ $v->name }}</option>
                        @endforeach
                    </select>
		        </span>
                <input type="text" name="" id="" placeholder="玩家昵称" style="width:250px" class="input-text">
                <input type="text" name="" id="" placeholder="玩家ID" style="width:100px" class="input-text">
                <button name="" id="" class="btn btn-success" type="submit"><i class="Hui-iconfont">&#xe665;</i> 查询
                </button>
            </div>
            <div class="mt-20">
                <table class="table table-border table-bordered table-bg table-hover table-sort table-responsive">
                    <thead>
                    <tr class="text-c">
                        <th>ID</th>
                        <th>拥有英雄</th>
                        <th>是否上阵</th>
                        <th>英雄等级</th>
                        <th>英雄品质</th>
                        <th>英雄星级</th>
                        <th>英雄技能情况</th>
                        <th>英雄宝石镶嵌情况</th>
                        <th>英雄碎片持有数量</th>
                    </tr>
                    </thead>
                    <tbody>

                    </tbody>
                </table>
            </div>
        </div>
    </section>
@endsection

@section('js')
    <script type="text/javascript">
        /*$('.table-sort').dataTable({
            "aaSorting": [[1, "desc"]],//默认第几个排序
            "bStateSave": true,//状态保存
            "pading": false,
            "aoColumnDefs": [
                {"orderable": false, "aTargets": [0, 7]}// 不参与排序的列
            ]
        });*/
    </script>
@endsection