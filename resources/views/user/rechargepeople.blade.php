@extends('common')

@include('nav.user')

@section('content')
    <section class="Hui-article-box">
        <nav class="breadcrumb"><i class="Hui-iconfont"></i> <a href="/" class="maincolor">首页</a>
            <span class="c-999 en">&gt;</span>
            <a href="/income/paytotal" class="maincolor">用户类</a>
            <span class="c-999 en">&gt;</span>
            <span class="c-666">用户分布</span>
            <a class="btn btn-success radius r" style="line-height:1.6em;margin-top:3px"
               href="javascript:location.replace(location.href);" title="刷新"><i class="Hui-iconfont">&#xe68f;</i></a>
        </nav>

        <div class="Hui-article">
            <form action="/user/rechargepeople" method="post" class="form form-horizontal">
                {!! csrf_field() !!}
                <div class="row cl">
                    <label class="form-label col-xs-1 col-sm-1">选择日期：</label>
                    <div class="formControls  skin-minimal">
                        <div class="radio-box">
                            <input type="radio" name="option-date" value="1" id="sex-1" {{ $option == 1?'checked':'' }}>
                            <label for="sex-1">本日</label>
                        </div>
                        <div class="radio-box">
                            <input type="radio" name="option-date" value="2" id="sex-2" {{ $option == 2?'checked':'' }}>
                            <label for="sex-2">本周</label>
                        </div>
                        <div class="radio-box">
                            <input type="radio" name="option-date" value="3" id="sex-3" {{ $option == 3?'checked':'' }}>
                            <label for="sex-3">本月</label>
                        </div>
                    </div>
                </div>
                <div class="row cl">
                    <label class="form-label col-xs-1 col-sm-1">起止日期：</label>
                    <div class="formControls col-xs-1 col-sm-1">
                        <input type="text" name="interval-date-start"
                               onfocus="WdatePicker({maxDate:'#F{$dp.$D(\'datemax\')||\'%y-%M-%d\'}'})"
                               id="datemin" class="input-text Wdate"
                               placeholder="{{ date('Y-m-d',$start/1000) }}">
                    </div>
                    <div class="formControls col-xs-1 col-sm-1" style="width: 5px">-</div>
                    <div class="formControls col-xs-1 col-sm-1">
                        <input type="text" name="interval-date-end"
                               onfocus="WdatePicker({minDate:'#F{$dp.$D(\'datemin\')}',maxDate:'%y-%M-%d'})"
                               id="datemax" class="input-text Wdate"
                               placeholder="{{ date('Y-m-d',$end/1000) }}">
                    </div>
                </div>

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
                    <label class="form-label col-xs-1 col-sm-1">显示区间：</label>
                    <div class="formControls  skin-minimal">
                        <div class="radio-box">
                            <input type="radio" name="type-date" value="1"
                                   id="interval-1" {{ isset($type) || $type == 1?'checked':'' }}>
                            <label for="interval-1">按天显示</label>
                        </div>
                        <div class="radio-box">
                            <input type="radio" name="type-date" value="2"
                                   id="interval-2" {{ $type == 2?'checked':'' }}>
                            <label for="interval-2">按段显示</label>
                        </div>
                    </div>
                </div>

                <div class="row cl">
                    <div class="col-xs-1 col-sm-1 col-xs-offset-1 col-sm-offset-1">
                        <button class="btn btn-success radius" type="submit"><i class="Hui-iconfont">&#xe665;</i>查询
                        </button>
                    </div>
                    <div class="col-xs-1 col-sm-1">
                        <a href="/user/rechargepeople?status=2" target="_blank" class="btn btn-secondary radius">导出</a>
                    </div>

                    <div class="col-xs-1 col-sm-1">
                        <a href="javascript:;" onclick="show_graph('最高在线/平均在线','/user/rechargepeople?status=3')"
                           class="btn btn-secondary radius">图形化显示</a>
                    </div>
                </div>
            </form>

            <div class="mt-20 col-xs-12 col-sm-12">
                <table class="table table-border table-bordered table-bg table-hover table-sort">
                    <thead>
                    <tr class="text-c">
                        <th>日期</th>
                        <th>充值用户总数</th>
                        <th>平均充值次数</th>
                        <th>平均每次充值金额</th>
                        <th>充值1次人数</th>
                        <th>充值2次人数</th>
                        <th>充值3次人数</th>
                        <th>充值4次人数</th>
                        <th>充值5次人数</th>
                        <th>充值6次人数</th>
                        <th>充值7次人数</th>
                        <th>充值8次人数</th>
                        <th>充值9次人数</th>
                        <th>充值10次人数</th>
                        <th>充值[11-15]次人数</th>
                        <th>充值[16-20]次人数</th>
                        <th>充值更多次人数</th>

                    </tr>
                    </thead>
                    <tbody>

                    @foreach($data as $k=>$v)
                        <tr class="text-c">
                            <td>{{ $k }}</td>
                            <td>{{ $v['recharge_user_sum'] }}</td>
                            <td>{{ $v['recharge_sum'] }}</td>
                            <td>{{ $v['aver_recharge'] }}</td>
                            <td>{{ $v['1c'] }}</td>
                            <td>{{ $v['2c'] }}</td>
                            <td>{{ $v['3c'] }}</td>
                            <td>{{ $v['4c'] }}</td>
                            <td>{{ $v['5c'] }}</td>
                            <td>{{ $v['6c'] }}</td>
                            <td>{{ $v['7c'] }}</td>
                            <td>{{ $v['8c'] }}</td>
                            <td>{{ $v['9c'] }}</td>
                            <td>{{ $v['10c'] }}</td>
                            <td>{{ $v['15c'] }}</td>
                            <td>{{ $v['20c'] }}</td>
                            <td>{{ $v['more'] }}</td>
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