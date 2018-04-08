@extends('common')
@include('nav.gm')
@section('content')
    <section class="Hui-article-box">
        <nav class="breadcrumb"><i class="Hui-iconfont"></i> <a href="/" class="maincolor">首页</a>
            <span class="c-999 en">&gt;</span>
            <a href="/gm/notice" class="maincolor">GM工具</a>
            <span class="c-999 en">&gt;</span>
            <span class="c-666">货币流转记录</span>
            <a class="btn btn-success radius r" style="line-height:1.6em;margin-top:3px"
               href="javascript:location.replace(location.href);" title="刷新"><i class="Hui-iconfont">&#xe68f;</i></a>
        </nav>

        <div class="page-container">
            <div class="text-c">
                <form action="/gm/query/property/flow" method="post">
                    {!! csrf_field() !!}
                    <span class="select-box inline">
                    <select name="pid" class="select">
                        <option value="0">选择渠道</option>
                        @foreach($pid as $k=>$v)
                            <option value="{{ $k+1 }}" {{ isset($parameter['pid']) && $parameter['pid']==$k+1?'selected':'' }}>{{ $v }}</option>
                        @endforeach
                    </select>
		        </span>
                    <span class="select-box inline">
                    <select name="serverId" class="select">
                        <option value="0">选择服务器</option>
                        @foreach($server as $k=>$v)
                            <option value="{{ $k }}" {{ isset($parameter['serverId']) && $parameter['serverId']==$k?'selected':'' }} >{{ $v }}</option>
                        @endforeach
                    </select>
		        </span>
                    <input type="text" onfocus="WdatePicker({maxDate:'#F{$dp.$D(\'logmax\')||\'%y-%M-%d\'}'})"
                           id="logmin"
                           class="input-text Wdate" style="width:120px;" placeholder="开始时间" name="beginTime"
                           value="{{ isset($parameter['beginTime'])? $parameter['beginTime']:''}}">
                    -
                    <input type="text" onfocus="WdatePicker({minDate:'#F{$dp.$D(\'logmin\')}',maxDate:'%y-%M-%d'})"
                           id="logmax" class="input-text Wdate" style="width:120px;" placeholder="结束时间" name="endTime"
                           value="{{ isset($parameter['endTime'])?$parameter['endTime']:'' }}">
                    <input type="text" name="userId" id="" placeholder="玩家ID" style="width:100px" class="input-text"
                           value="{{ isset($parameter['userId'])?$parameter['userId']:'' }}">
                    <button name="" id="" class="btn btn-success" type="submit"><i class="Hui-iconfont">&#xe665;</i> 查询
                    </button>
                </form>
            </div>

            @if ($errors->any())
                <div class="text-c">
                    @foreach ($errors->all() as $error)
                        <span class="select-box inline c-red">{{ $error }}</span>
                    @endforeach
                </div>
            @endif

            <div class="mt-20">
                <table class="table table-border table-bordered table-bg table-hover table-sort table-responsive">
                    <thead>
                    <tr class="text-c">
                        <th>渠道ID</th>
                        <th>服务器</th>
                        <th>用户ID</th>
                        <th>角色ID</th>
                        <th>角色名称</th>
                        <th>道具ID</th>
                        <th>道具数量</th>
                        <th>剩余数量</th>
                        <th>操作时间</th>
                    </tr>
                    </thead>
                    <tbody>
                    @foreach($data as $v)
                        <tr>
                            <td>{{ $v['pid'] }}</td>
                            <td>{{ $server[$v['serverId']] }}</td>
                            <td>{{ $v['userId'] }}</td>
                            <td>{{ $v['roleId'] }}</td>
                            <td>{{ $v['roleName'] }}</td>
                            <td>{{ $v['itemId'] }}</td>
                            <td>{{ $v['itemNum'] }}</td>
                            <td>{{ $v['leftNum'] }}</td>
                            <td>{{ date('Y-m-d',$v['logTime']/1000) }}</td>
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
            "aaSorting": [[8, "desc"]],//默认第几个排序
        });
    </script>
@endsection