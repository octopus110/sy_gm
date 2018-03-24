@extends('common')

@include('nav.online')

@section('content')
    <section class="Hui-article-box">
        <nav class="breadcrumb"><i class="Hui-iconfont"></i> <a href="/online/total" class="maincolor">在线类</a>
            <span class="c-999 en">&gt;</span>
            <span class="c-666">总实时在线</span>
            <a class="btn btn-success radius r" style="line-height:1.6em;margin-top:3px"
               href="javascript:location.replace(location.href);" title="刷新"><i class="Hui-iconfont">&#xe68f;</i></a>
        </nav>
        <div class="Hui-article">
            <article class="cl pd-20">
                {{--<p class="f-20 text-success">此数据每5分钟更新一次</p>--}}
                <p>当前时间：{{ date('Y-m-d H:i:s') }}</p>
                <table class="table table-border table-bordered table-bg mt-20">
                    <thead>
                    <tr>
                        <th colspan="2" scope="col">总实时在线信息</th>
                    </tr>
                    </thead>
                    <tr>
                        <td width="25%">服务器ID</td>
                        <td>{{ $data['serverId'] }}</td>
                    </tr>
                    <tr>
                        <td>当前在线总人数</td>
                        <td>{{ $data['onlineUsers'] }}</td>
                    </tr>
                    <tr>
                        <td>当前服务器容量</td>
                        <td>{{ $data['offlineCacheSize'] }}</td>
                    </tr>
                    <tr>
                        <td>最近一次更新时间</td>
                        <td>{{ date('Y-m-d H:i:s',$data['logTime']/1000) }}</td>
                    </tr>
                    </tbody>
                </table>
            </article>
        </div>
    </section>
@endsection