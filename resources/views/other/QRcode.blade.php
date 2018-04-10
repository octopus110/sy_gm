@extends('common')
@include('nav.other')
@section('content')
    <section class="Hui-article-box">
        <nav class="breadcrumb"><i class="Hui-iconfont"></i> <a href="/" class="maincolor">首页</a>
            <span class="c-999 en">&gt;</span>
            <a href="/gm/index" class="maincolor">其他</a>
            <span class="c-999 en">&gt;</span>
            <span class="c-666">生成二维码</span>
            <a class="btn btn-success radius r" style="line-height:1.6em;margin-top:3px"
               href="javascript:location.replace(location.href);" title="刷新"><i class="Hui-iconfont">&#xe68f;</i></a>
        </nav>

        <div class="page-container">
            <div class="row cl">
                <label class="form-label col-xs-1 col-sm-1 text-r">扫描统计:</label>
                <label class="form-label col-xs-1 col-sm-1">Android：{{ $data['android'] }} 次</label>
                <label class="form-label col-xs-1 col-sm-1">ios：{{ $data['ios'] }} 次</label>
                <label class="form-label col-xs-1 col-sm-1">总计：{{ $data['sum'] }} 次</label>
            </div>
            <div class="row cl">
                &nbsp;
            </div>

            <form action="javascript:;" class="form form-horizontal" id="form-admin-add">
                <div class="row cl">
                    <label class="form-label col-xs-1 col-sm-1">Android：</label>
                    <div class="formControls col-xs-4 col-sm-4">
                        <input type="text" class="input-text" value="" placeholder="网址必须以http或https开头" id="android"
                               name="android">
                    </div>
                </div>

                <div class="row cl">
                    <label class="form-label col-xs-1 col-sm-1">IOS：</label>
                    <div class="formControls col-xs-4 col-sm-4">
                        <input type="text" class="input-text" value="" placeholder="网址必须以http或https开头" id="ios"
                               name="ios">
                    </div>
                </div>

                <div class="row cl">
                    <label class="form-label col-xs-1 col-sm-1">尺寸：</label>
                    <div class="formControls col-xs-5 col-sm-5">
                        <div class="radio-box">
                            <input name="size[]" type="checkbox" id="size-2" value="8" checked>
                            <label for="size-2">8cm</label>
                        </div>
                        <div class="radio-box">
                            <input name="size[]" type="checkbox" value="12" id="size-3">
                            <label for="size-3">12cm</label>
                        </div>
                        <div class="radio-box">
                            <input name="size[]" type="checkbox" value="15" id="size-4">
                            <label for="size-4">15cm</label>
                        </div>
                        <div class="radio-box">
                            <input name="size[]" type="checkbox" value="30" id="size-5">
                            <label for="size-5">30cm</label>
                        </div>
                    </div>
                </div>

                <div class="row cl">
                    <div class="formControls col-xs-4 col-sm-4 col-sm-offset-1">
                        <input class="btn btn-primary radius" type="submit" value="&nbsp;&nbsp;生成&nbsp;&nbsp;"
                               onclick="create_qr(this)">
                    </div>
                </div>
            </form>

            <div class="row cl" id="qr">

            </div>
        </div>
    </section>
@endsection

@section('js')
    <script type="text/javascript">
        /*生成二维码*/
        function create_qr(obj) {
            layer.confirm('确认要生成吗？', function () {

                var size_input = $('input[type=checkbox]');
                var size_input_length = size_input.length;

                var size = [];

                for (var i = 0; i < size_input_length; i++) {
                    if (size_input.eq(i).is(':checked')) {
                        size.push(size_input.eq(i).val())
                    }
                }

                $.ajax({
                    type: 'post',
                    url: '/other/QRcode',
                    dataType: 'json',
                    data: {
                        '_token': "{{ csrf_token() }}",
                        'android': $('input[name=android]').val(),
                        'ios': $('input[name=ios]').val(),
                        'size': size,
                    },
                    success: function (data) {
                        if (data.status) {
                            var _html = '';
                            for (var i = 0; i < data.data.length; i++) {
                                _html += "<div class=\"formControls col-xs-3 col-sm-3\">" +
                                    "<img src='" + data.data[i][1] + "' alt=\"\" width=\"100%\"/>" +
                                    "<p class=\"text-c\">" + data.data[i][0] + "</p>" +
                                    "</div>";
                            }
                            $('#qr').html(_html);
                            $(obj).parents("tr").remove();
                            layer.msg('已生成!', {icon: 1, time: 1000});
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