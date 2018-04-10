<!DOCTYPE HTML>
<html>
<head>
    <meta charset="utf-8">
    <meta name="renderer" content="webkit|ie-comp|ie-stand">
    <meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">
    <meta name="viewport"
          content="width=device-width,initial-scale=1,minimum-scale=1.0,maximum-scale=1.0,user-scalable=no"/>
    <meta http-equiv="Cache-Control" content="no-siteapp"/>
    <link rel="Bookmark" href="favicon.ico">
    <link rel="Shortcut Icon" href="favicon.ico"/>
    <!--[if lt IE 9]>
    <script type="text/javascript" src="/lib/html5.js"></script>
    <script type="text/javascript" src="/lib/respond.min.js"></script>
    <![endif]-->
    <link rel="stylesheet" type="text/css" href="/static/h-ui/css/H-ui.min.css"/>
    <link rel="stylesheet" type="text/css" href="/static/h-ui.admin/css/H-ui.admin.css"/>
    <link rel="stylesheet" type="text/css" href="/lib/Hui-iconfont/1.0.8/iconfont.css"/>
    <link rel="stylesheet" type="text/css" href="/static/h-ui.admin/skin/default/skin.css" id="skin"/>
    <link rel="stylesheet" type="text/css" href="/static/h-ui.admin/css/style.css"/>
    <!--[if IE 6]>
    <script type="text/javascript" src="http://lib.h-ui.net/DD_belatedPNG_0.0.8a-min.js"></script>
    <script>DD_belatedPNG.fix('*');</script>
    <![endif]-->
</head>
<body>
<div class="page-container">
    <article class="page-container">
        <form action="{{ $ip.'orderRole' }}" method="post" class="form form-horizontal" id="form-admin-add">
            <input type="hidden" name="packId" value="0">
            <div class="row cl">
                <label class="form-label col-xs-4 col-sm-3">服务器ID：</label>
                <div class="formControls col-xs-8 col-sm-9">
                    <input type="text" class="input-text" name="serverId" value="{{ $serverId }}" readonly/>
                </div>
            </div>

            <div class="row cl">
                <label class="form-label col-xs-4 col-sm-3">渠道ID：</label>
                <div class="formControls col-xs-8 col-sm-9">
                    <input type="text" class="input-text" name="pid" value="{{ $pid }}" readonly/>
                </div>
            </div>

            <div class="row cl">
                <label class="form-label col-xs-4 col-sm-3">账号ID：</label>
                <div class="formControls col-xs-8 col-sm-9">
                    <input type="text" class="input-text" name="passportId" value="{{ $passportId }}" readonly/>
                </div>
            </div>

            <div class="row cl">
                <label class="form-label col-xs-4 col-sm-3">角色ID：</label>
                <div class="formControls col-xs-8 col-sm-9">
                    <input type="text" class="input-text" name="roleId" value="{{ $roleId }}" readonly/>
                </div>
            </div>

            <div class="row cl">
                <label class="form-label col-xs-4 col-sm-3"><span class="c-red">*</span>充值时间：</label>
                <div class="formControls col-xs-8 col-sm-9">
                    <input type="text"
                           onfocus="WdatePicker({dateFmt:'yyyy-MM-dd HH:mm:ss',maxDate:'#F{$dp.$D(\'datemax\')||\'%y-%M-%d\'}'})"
                           id="datemin" class="input-text Wdate" name="time" value=""/>
                    <input type="text"
                           onfocus="WdatePicker({dateFmt:'yyyy-MM-dd HH:mm:ss',minDate:'#F{$dp.$D(\'datemin\')}'})"
                           id="datemax" class="input-text Wdate" style="display: none;"/>
                </div>
            </div>

            <div class="row cl">
                <label class="form-label col-xs-4 col-sm-3"><span class="c-red">*</span>内购商品：</label>
                <div class="formControls col-xs-8 col-sm-9"> <span class="select-box">
				<select name="goodsId" class="select">
					<option value="0">选择商品</option>
                    @foreach($good as $v)
                        <option value="{{ $v['id'] }}">{{ $v['nameText'] }}</option>
                    @endforeach
				</select>
				</span></div>
            </div>

            <div class="row cl">
                <label class="form-label col-xs-4 col-sm-3">第三方订单号：</label>
                <div class="formControls col-xs-8 col-sm-9">
                    <input type="text" class="input-text" name="order" value=""/>
                </div>
            </div>

            <div class="row cl">
                <div class="col-xs-8 col-sm-9 col-xs-offset-4 col-sm-offset-3">
                    <input class="btn btn-primary radius" type="submit" value="&nbsp;&nbsp;提交&nbsp;&nbsp;">
                </div>
            </div>
        </form>
    </article>
</div>

<script type="text/javascript" src="/lib/jquery/1.9.1/jquery.min.js"></script>
<script type="text/javascript" src="/lib/layer/2.4/layer.js"></script>
<script type="text/javascript" src="/static/h-ui/js/H-ui.js"></script>
<script type="text/javascript" src="/static/h-ui.admin/js/H-ui.admin.page.js"></script>
<script type="text/javascript" src="/lib/My97DatePicker/4.8/WdatePicker.js"></script>
<script type="text/javascript" src="/lib/jquery.validation/1.14.0/jquery.validate.js"></script>
<script type="text/javascript" src="/lib/jquery.validation/1.14.0/validate-methods.js"></script>
<script type="text/javascript" src="/lib/jquery.validation/1.14.0/messages_zh.js"></script>
</body>
</html>