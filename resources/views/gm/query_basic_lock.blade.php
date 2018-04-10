<!DOCTYPE HTML>
<html>
<head>
    <meta charset="utf-8">
    <meta name="renderer" content="webkit|ie-comp|ie-stand">
    <meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">
    <meta name="viewport"
          content="width=device-width,initial-scale=1,minimum-scale=1.0,maximum-scale=1.0,user-scalable=no"/>
    <meta http-equiv="Cache-Control" content="no-siteapp"/>

    <link rel="stylesheet" type="text/css" href="/static/h-ui/css/H-ui.min.css"/>
    <link rel="stylesheet" type="text/css" href="/static/h-ui.admin/css/H-ui.admin.css"/>
    <link rel="stylesheet" type="text/css" href="/lib/Hui-iconfont/1.0.8/iconfont.css"/>
    <link rel="stylesheet" type="text/css" href="/static/h-ui.admin/skin/default/skin.css" id="skin"/>
    <link rel="stylesheet" type="text/css" href="/static/h-ui.admin/css/style.css"/>
</head>
<body>
<div class="page-container">
    <article class="page-container">
        <form action="{{ $ip.'lockUser' }}" method="post" class="form form-horizontal" id="form-admin-add">
            <div class="row cl">
                <label class="form-label col-xs-4 col-sm-3">类型：</label>
                <div class="formControls col-xs-8 col-sm-9 skin-minimal">
                    <div class="radio-box">
                        <input name="type" type="radio" id="sex-1" value="0" checked>
                        <label for="sex-1">解除锁定</label>
                    </div>
                    <div class="radio-box">
                        <input type="radio" id="sex-2" name="type" value="1">
                        <label for="sex-2">临时冻结</label>
                    </div>
                    <div class="radio-box">
                        <input type="radio" id="sex-3" name="type" value="2">
                        <label for="sex-3">永久冻结</label>
                    </div>
                </div>
            </div>
            <div class="row cl">
                <label class="form-label col-xs-4 col-sm-3">禁言时间（天：小时：分钟）：</label>
                <div class="formControls col-xs-8 col-sm-9">
                    <input type="text" class="input-text" name="minute" placeholder="例如：05:12:34">
                </div>
            </div>

            <div class="row cl">
                <label class="form-label col-xs-4 col-sm-3">玩家ID：</label>
                <div class="formControls col-xs-8 col-sm-9">
                    <input type="text" class="input-text" value="{{ $userId }}" name="userId" readonly>
                </div>
            </div>

            <div class="row cl">
                <label class="form-label col-xs-4 col-sm-3">理由：</label>
                <div class="formControls col-xs-8 col-sm-9">
                    <textarea name="reason" class="textarea"></textarea>
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
</body>
</html>