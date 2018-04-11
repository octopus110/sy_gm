@extends('common')
@include('nav.gm')
@section('content')
    <section class="Hui-article-box">
        <nav class="breadcrumb"><i class="Hui-iconfont"></i> <a href="/" class="maincolor">首页</a>
            <span class="c-999 en">&gt;</span>
            <a href="/gm/index" class="maincolor">GM工具</a>
            <span class="c-999 en">&gt;</span>
            <span class="c-666">道具发放</span>
            <a class="btn btn-success radius r" style="line-height:1.6em;margin-top:3px"
               href="javascript:location.replace(location.href);" title="刷新"><i class="Hui-iconfont">&#xe68f;</i></a>
        </nav>

        <div class="page-container">
            <form action="{{ $ip.'pushSendMail' }}" method="post" class="form form-horizontal"
                  id="form-article-add">
                <input type="hidden" name="mailType" value="2"/>
                <input type="hidden" name="iconId" value="1"/>
                <div class="row cl">
                    <label class="form-label col-xs-4 col-sm-2">服务器：</label>
                    <div class="formControls col-xs-8 col-sm-9"> <span class="select-box">
				<select name="serverId" class="select">
					<option value="0">选择服务器</option>
                    @foreach($server as $k=>$v)
                        <option value="22|{{ $k }}">{{ $v }}</option>
                    @endforeach
				</select>
				</span></div>
                </div>

                <div class="row cl">
                    <label class="form-label col-xs-4 col-sm-2">发送人：</label>
                    <div class="formControls col-xs-8 col-sm-9">
                        <input type="text" class="input-text" value="" name="sender" placeholder="小于8个汉字"/>
                    </div>
                </div>

                <div class="row cl">
                    <label class="form-label col-xs-4 col-sm-2">接受者类型：</label>
                    <div class="formControls col-xs-8 col-sm-9 skin-minimal">
                        <div class="radio-box">
                            <input name="type" type="radio" id="sex-1" value="1">
                            <label for="sex-1">角色ID</label>
                        </div>
                        <div class="radio-box">
                            <input name="type" type="radio" id="sex-2" value="2">
                            <label for="sex-2">角色昵称</label>
                        </div>
                    </div>
                </div>

                <div class="row cl">
                    <label class="form-label col-xs-4 col-sm-2">接受者列表(用"|"分割多个)：</label>
                    <div class="formControls col-xs-8 col-sm-9">
                        <textarea class="textarea" placeholder="一次接收人数不易太多" name="list"></textarea>
                    </div>
                </div>

                <div class="row cl">
                    <label class="form-label col-xs-4 col-sm-2">标题：</label>
                    <div class="formControls col-xs-8 col-sm-9">
                        <input type="text" class="input-text" value="" name="title" placeholder="小于8个汉字">
                    </div>
                </div>

                <div class="row cl">
                    <label class="form-label col-xs-4 col-sm-2">正文：</label>
                    <div class="formControls col-xs-8 col-sm-9">
                        <textarea class="textarea" placeholder="行数和附件数的总和小于16，每行小于16个汉字" name="content"></textarea>
                    </div>
                </div>

                <div class="row cl">
                    <label class="form-label col-xs-4 col-sm-2">已选择的附件：</label>
                    <div class="formControls col-xs-8 col-sm-9">
                        <input type="text" class="input-text" value="" name="affix"
                               placeholder="附件数和正文行数的总和小于16，一次附件数不易太多"
                               id="goods_selected">
                    </div>
                </div>

                <div class="row cl">
                    <label class="col-xs-12 col-sm-12 text-c c-red">从下面选择你需要发放的物品↓</label>
                </div>

                <div class="row cl">
                    <label class="form-label col-xs-4 col-sm-2">物品类型：</label>
                    <div class="formControls col-xs-4 col-sm-4">
                        <span class="select-box">
                            <select name="" class="select" onchange="get_goods(this)">
                                <option value="0">选择物品类型</option>
                                <option value="1">资产</option>
                                <option value="2">道具</option>
                                <option value="3">碎片</option>
                            </select>
                        </span>
                    </div>
                </div>

                <div class="row cl">
                    <label class="form-label col-xs-4 col-sm-2">确认物品：</label>
                    <div class="formControls col-xs-1 col-sm-1">
                        <input type="number" class="input-text" name="" min="1" placeholder="数量" id="goods_sum"/>
                    </div>
                    <div class="formControls col-xs-7 col-sm-8" id="radio-box"
                         style="max-height: 200px; overflow: auto"></div>
                </div>

                <div class="row cl">
                    <div class="col-xs-8 col-sm-9 col-xs-offset-2 col-sm-offset-2">
                        <input class="btn btn-info radius" type="button" value="&nbsp;&nbsp;添加&nbsp;&nbsp;"
                               onclick="goods_add()">
                    </div>
                </div>

                <div class="row cl">
                    <div class="col-xs-8 col-sm-9 col-xs-offset-1 col-sm-offset-1">
                        <input class="btn btn-primary radius" type="submit" value="&nbsp;&nbsp;发放&nbsp;&nbsp;">
                    </div>
                </div>
            </form>
        </div>
    </section>
@endsection

@section('js')
    <script type="text/javascript">
        function get_goods(that) {
            var vs = $(that).val();

            $.ajax({
                type: 'get',
                url: "/gm/grant/ajax",
                dataType: 'json',
                data: {
                    'type': vs
                },
                success: function (data) {
                    var length = data.length;
                    var _html = '';
                    for (var i = 0; i < length; i++) {
                        _html += "<div class='radio-box'>" +
                            "<input name='goods' type='radio' value='" + data[i]['inputValue'] + "' id='goods_" + data[i]['inputValue'] + "'>" +
                            "<label for='goods_" + data[i]['inputValue'] + "'>" + data[i]['boxLabel'] + "</label>" +
                            "</div>";
                    }

                    $("#radio-box").html(_html);
                },
                error: function (data) {
                    console.log(data);
                },
            });
        }

        function goods_add() {
            var goods_sum = $("#goods_sum").val();
            var goods = $("#radio-box").find("input[name=goods]:checked").val();

            var item = goods + ':' + goods_sum;

            var goods_selected = $('#goods_selected');

            if (goods_selected.val() != '') {
                goods_selected.val(goods_selected.val() + '|' + item);
            } else {
                goods_selected.val(item);
            }
        }
    </script>
@endsection