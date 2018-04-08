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
    <script type="text/javascript" src="lib/html5.js"></script>
    <script type="text/javascript" src="lib/respond.min.js"></script>
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
    <title>新增公告</title>
</head>
<body>
<article class="page-container">
    <div class="row cl">
        <label class="form-label col-xs-4 col-sm-2"></label>
        <div class="formControls col-xs-8 col-sm-9">
            @if (count($errors) > 0)
                @foreach ($errors->all() as $error)
                    <div class="c-red">{{ $error }}</div>
                @endforeach
            @endif
        </div>
    </div>
    <form class="form form-horizontal" method="post" action="/gm/notice/new_edit">
        {!! csrf_field() !!}
        <input type="hidden" name="id" value="{{ isset($data->id)?$data->id:''}}"/>
        <div class="row cl">
            <label class="form-label col-xs-4 col-sm-2"><span class="c-red">*</span>文章标题：</label>
            <div class="formControls col-xs-8 col-sm-9">
                <input type="text" class="input-text" value="{{ isset($data->title['cn'])?$data->title['cn']:'' }}"
                       name="title_cn">
            </div>
        </div>
        <div class="row cl">
            <label class="form-label col-xs-4 col-sm-2">英文标题：</label>
            <div class="formControls col-xs-8 col-sm-9">
                <input type="text" class="input-text" value="{{ isset($data->title['en'])?$data->title['en']:'' }}"
                       name="title_en">
            </div>
        </div>
        <div class="row cl">
            <label class="form-label col-xs-4 col-sm-2">排序值：</label>
            <div class="formControls col-xs-8 col-sm-9">
                <input type="text" class="input-text" value="{{ isset($data->weight)?$data->weight:'' }}" name="weight"
                       checkType="int">
            </div>
        </div>
        <div class="row cl">
            <label class="form-label col-xs-4 col-sm-2"><span class="c-red">*</span>开始日期：</label>
            <div class="formControls col-xs-8 col-sm-9">
                <input type="text"
                       onfocus="WdatePicker({dateFmt:'yyyy-MM-dd HH:mm:ss',maxDate:'#F{$dp.$D(\'datemax\')||\'%y-%M-%d\'}'})"
                       id="datemin" class="input-text Wdate" name="beginTime"
                       value="{{ isset($data->beginTime)?$data->beginTime:'' }}">
            </div>
        </div>
        <div class="row cl">
            <label class="form-label col-xs-4 col-sm-2"><span class="c-red">*</span>结束日期：</label>
            <div class="formControls col-xs-8 col-sm-9">
                <input type="text"
                       onfocus="WdatePicker({dateFmt:'yyyy-MM-dd HH:mm:ss',minDate:'#F{$dp.$D(\'datemin\')}'})"
                       id="datemax" class="input-text Wdate" name="endTime"
                       value="{{ isset($data->endTime)?$data->endTime:'' }}">
            </div>
        </div>
        <div class="row cl">
            <label class="form-label col-xs-4 col-sm-2"><span class="c-red">*</span>文章内容：</label>
            <div class="formControls col-xs-8 col-sm-9">
                <textarea class="textarea" style="width:100%; height:300px; resize:none" name="contant"> {!! isset($data->contant)?$data->contant :"中英文用****分割，中文在上英文在下，例如
中文内容。
****
Here is the English content." !!}</textarea>
            </div>
        </div>
        <div class="row cl">
            <label class="form-label col-xs-4 col-sm-2"><span class="c-red">*</span>显示语言：</label>
            <div class="formControls col-xs-8 col-sm-9"> <span class="select-box">
				<select name="language" class="select">
					<option value="cn" {{ isset($data->language) && $data->language=='cn'?'selected':'' }}>中文</option>
					<option value="en" {{ isset($data->language) && $data->language=='en'?'selected':'' }}>英文</option>
				</select>
				</span></div>
        </div>
        <div class="row cl">
            <div class="col-xs-8 col-sm-9 col-xs-offset-4 col-sm-offset-2">
                <button class="btn btn-primary radius" type="submit" name="status"
                        value="1">
                    <i class="Hui-iconfont"> &#xe632;</i> 保存
                </button>
                <button class="btn btn-secondary radius" type="submit" name="status"
                        value="0">
                    <i class="Hui-iconfont"> &#xe632;</i> 保存草稿
                </button>
            </div>
        </div>
    </form>
</article>

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