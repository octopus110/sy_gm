@extends('common')

@include('nav.income')

@section('content')
    <section class="Hui-article-box">
        <nav class="breadcrumb"><i class="Hui-iconfont"></i> <a href="/online/total" class="maincolor">在线类</a>
            <span class="c-999 en">&gt;</span>
            <span class="c-666">历史在线</span>
            <span class="c-999 en">&gt;</span>
            <span class="c-666">付费细节</span>
            <a class="btn btn-success radius r" style="line-height:1.6em;margin-top:3px"
               href="javascript:location.replace(location.href);" title="刷新"><i class="Hui-iconfont">&#xe68f;</i></a>
        </nav>
        <div class="Hui-article">
            <article class="cl pd-20">
                <div class="text-c">
                    选择日期
                    <form action="/income/paydetail" method="post">
                        {!! csrf_field() !!}
                        <input type="text" name="start"
                               onfocus="WdatePicker({maxDate:'#F{$dp.$D(\'logmax\')||\'%y-%M-%d\'}'})"
                               id="logmin" class="input-text Wdate" style="width:120px;" placeholder="开始时间">

                        <input type="text" name="end"
                               onfocus="WdatePicker({minDate:'#F{$dp.$D(\'logmin\')}',maxDate:'%y-%M-%d'})"
                               id="logmax" class="input-text Wdate" style="width:120px; display: none" placeholder="时间">
                        <button class="btn btn-success" type="submit"><i class="Hui-iconfont">&#xe665;</i> 查询</button>
                    </form>
                </div>

                <div class="mt-20">
                    <table class="table table-border table-bordered table-bg table-hover table-sort">
                        <thead>
                        <tr class="text-c">
                            <th>账号</th>
                            <th>渠道ID</th>
                            <th>服务器</th>
                            <th>角色ID</th>
                            <th>角色名</th>
                            <th>充值金额</th>
                            <th>充值时间点</th>
                        </tr>
                        </thead>
                        <tbody>
                        @foreach($data as $v)
                            <tr class="text-c">
                                <td>{{ $v['userId'] }}</td>
                                <td>{{ $v['pid'] }}</td>
                                <td>{{ $v['serverId'] }}</td>
                                <td>{{ $v['roleId'] }}</td>
                                <td>{{ $v['roleName'] }}</td>
                                <td>{{ $v['rechargeRMB'] }}</td>
                                <td>{{ $v['payTime'] }}</td>
                            </tr>
                        @endforeach
                        </tbody>
                    </table>
                </div>
            </article>
        </div>
        </div>
    </section>
@endsection

@section('js')
    <script type="text/javascript">
        $('.table-sort').dataTable({
            "aaSorting": [[1, "desc"]],//默认第几个排序
            "bStateSave": true,//状态保存
            "aoColumnDefs": [
                {"orderable": false, "aTargets": [0, 6]}// 不参与排序的列
            ]
        });
    </script>
@endsection