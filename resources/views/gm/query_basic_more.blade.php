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
        <form class="form form-horizontal">
            <div class="row cl">
                <label class="form-label col-xs-1 col-sm-1">真实姓名：</label>
                <div class="formControls col-xs-4 col-sm-4">
                    <input type="text" class="input-text" value="{{ $data['name'] }}"/>
                </div>

                <label class="form-label col-xs-1 col-sm-1">角色禁言状态：</label>
                <div class="formControls col-xs-4 col-sm-4">
                    <input type="text" class="input-text" value="{{ $data['shutups'] }}"/>
                </div>
            </div>

            <div class="row cl">
                <label class="form-label col-xs-1 col-sm-1">身份证：</label>
                <div class="formControls col-xs-4 col-sm-4">
                    <input type="text" class="input-text" value="{{ $data['number'] }}"/>
                </div>
                <label class="form-label col-xs-1 col-sm-1">角色冻结状态：</label>
                <div class="formControls col-xs-4 col-sm-4">
                    <input type="text" class="input-text" value="{{ $data['freeze'] }}"/>
                </div>
            </div>

            <div class="row cl">
                <label class="form-label col-xs-1 col-sm-1">账号ID：</label>
                <div class="formControls col-xs-4 col-sm-4">
                    <input type="text" class="input-text" value="{{ $data['passportId'] }}"/>
                </div>
                <label class="form-label col-xs-1 col-sm-1">用户ID：</label>
                <div class="formControls col-xs-4 col-sm-4">
                    <input type="text" class="input-text" value="{{ $data['userId'] }}"/>
                </div>
            </div>

            <div class="row cl">
                <label class="form-label col-xs-1 col-sm-1">换名次数：</label>
                <div class="formControls col-xs-4 col-sm-4">
                    <input type="text" class="input-text" value="{{ $data['changeNameTimes'] }}"/>
                </div>
                <label class="form-label col-xs-1 col-sm-1">角色ID：</label>
                <div class="formControls col-xs-4 col-sm-4">
                    <input type="text" class="input-text" value="{{ $data['roleId'] }}"/>
                </div>
            </div>

            <div class="row cl">
                <label class="form-label col-xs-1 col-sm-1">角色姓名：</label>
                <div class="formControls col-xs-4 col-sm-4">
                    <input type="text" class="input-text" value="{{ $data['roleName'] }}"/>
                </div>
                <label class="form-label col-xs-1 col-sm-1">头像ID：</label>
                <div class="formControls col-xs-4 col-sm-4">
                    <input type="text" class="input-text" value="{{ $data['avatarId'] }}"/>
                </div>
            </div>

            <div class="row cl">
                <label class="form-label col-xs-1 col-sm-1">头像边框ID：</label>
                <div class="formControls col-xs-4 col-sm-4">
                    <input type="text" class="input-text" value="{{ $data['avatarFrameId'] }}"/>
                </div>
                <label class="form-label col-xs-1 col-sm-1">角色等级：</label>
                <div class="formControls col-xs-4 col-sm-4">
                    <input type="text" class="input-text" value="{{ $data['roleLevel'] }}"/>
                </div>
            </div>

            <div class="row cl">
                <label class="form-label col-xs-1 col-sm-1">角色经验：</label>
                <div class="formControls col-xs-4 col-sm-4">
                    <input type="text" class="input-text" value="{{ $data['exp'] }}"/>
                </div>
                <label class="form-label col-xs-1 col-sm-1">VIP等级：</label>
                <div class="formControls col-xs-4 col-sm-4">
                    <input type="text" class="input-text" value="{{ $data['vipLevel'] }}"/>
                </div>
            </div>

            <div class="row cl">
                <label class="form-label col-xs-1 col-sm-1">总充值钻石：</label>
                <div class="formControls col-xs-4 col-sm-4">
                    <input type="text" class="input-text" value="{{ $data['totalDiamondGet'] }}"/>
                </div>
                <label class="form-label col-xs-1 col-sm-1">竞技场当前排名：</label>
                <div class="formControls col-xs-4 col-sm-4">
                    <input type="text" class="input-text" value="{{ $data['ranking'] }}"/>
                </div>
            </div>

            <div class="row cl">
                <label class="form-label col-xs-1 col-sm-1">碎片背包：</label>
                <div class="formControls col-xs-4 col-sm-4">
                    <textarea name="" style='height:200px' class="textarea">{{ $data['fragmentBag'] }}</textarea>
                </div>
                <label class="form-label col-xs-1 col-sm-1">物品背包：</label>
                <div class="formControls col-xs-4 col-sm-4">
                    <textarea name="" style='height:200px' class="textarea">{{ $data['itemBag'] }}</textarea>
                </div>
            </div>

            <div class="row cl">
                <label class="form-label col-xs-1 col-sm-1">队伍阵容：</label>
                <div class="formControls col-xs-4 col-sm-4">
                    <textarea name="" style='height:200px' class="textarea">{{ $data['team'] }}</textarea>
                </div>
                <label class="form-label col-xs-1 col-sm-1">资产：</label>
                <div class="formControls col-xs-4 col-sm-4">
                    <textarea name="" style='height:200px' class="textarea">{{ $data['assets'] }}</textarea>
                </div>
            </div>

            <div class="row cl">
                <label class="form-label col-xs-1 col-sm-1">英雄：</label>
                <div class="formControls col-xs-4 col-sm-4">
                    <textarea name="" style='height:200px' class="textarea">{{ $data['heros'] }}</textarea>
                </div>
                <label class="form-label col-xs-1 col-sm-1">玩家任务：</label>
                <div class="formControls col-xs-4 col-sm-4">
                    <textarea name="" style='height:200px' class="textarea">{{ $data['task'] }}</textarea>
                </div>
            </div>

            <div class="row cl">
                <label class="form-label col-xs-1 col-sm-1">普通副本进度：</label>
                <div class="formControls col-xs-4 col-sm-4">
                    <textarea name="" style='height:200px' class="textarea">{{ $data['roid'] }}</textarea>
                </div>
                <label class="form-label col-xs-1 col-sm-1">活动副本进度：</label>
                <div class="formControls col-xs-4 col-sm-4">
                    <textarea name="" style='height:200px' class="textarea">{{ $data['actRoid'] }}</textarea>
                </div>
            </div>
        </form>
    </article>
</div>
</body>
</html>