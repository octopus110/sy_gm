<?php

Route::any('/login', 'loginController@login');
Route::any('/quit', 'loginController@quit');

Route::group(['middleware' => 'loginAuthentication'], function () {
    Route::get('/', 'onlineController@index');
    //在线类
    Route::group(['prefix' => 'online'], function () {
        Route::any('/total', 'onlineController@total');//总实时在线
        //Route::get('/capacity', 'onlineController@capacity');//实时服务器容量
        Route::any('/maxaverage', 'onlineController@maxaverage');//最高平均在线
        Route::any('/dau', 'onlineController@dau');//DAU
        Route::any('/timedistribution', 'onlineController@timedistribution');//每日在线时段分布
        Route::any('/lengthdistribution', 'onlineController@lengthdistribution');//平均在线时长分布
        Route::any('/frequency', 'onlineController@frequency');//平均在线时长分布
    });
    //收入类
    Route::group(['prefix' => 'income'], function () {
        Route::any('paytotal', 'incomeController@paytotal');//某时间段充值总况
        Route::any('timelypay', 'incomeController@timelypay');//每小时充值总况
        Route::any('payKTV', 'incomeController@payKTV');//LTV值
        Route::any('paydistribution', 'incomeController@paydistribution');//付费分布
        Route::any('userPayment', 'incomeController@userPayment');//某个时间段玩家付费排行榜
        Route::any('paydetail', 'incomeController@paydetail');//支付明细
    });
    //用户类
    Route::group(['prefix' => 'user'], function () {
        Route::any('total', 'userController@total');//用户某时间总况
        Route::any('realtimertotal', 'userController@realtimertotal');//实时用户总况
        Route::any('newkeep', 'userController@newkeep');//新增用户留存率
        Route::any('activekeep', 'userController@activekeep');//活跃用户留存率
        Route::any('active', 'userController@active');//活跃用户
        Route::any('chum', 'userController@chum');//流失用户数
        Route::any('backflow', 'userController@backflow');//回流
        Route::any('rechargeuser', 'userController@rechargeuser');//充值用户每日情况
        Route::any('rechargelimit', 'userController@rechargelimit');//充值额度分布
        Route::any('rechargepeople', 'userController@rechargepeople');//充值额度分布
        Route::any('paychum', 'userController@paychum');//流失用户数
        Route::any('paybackflow', 'userController@paybackflow');//回流
    });
    //GM工具
    Route::group(['prefix' => 'gm'], function () {
        Route::any('notice', 'gmController@notice');//登录前公告
        Route::any('notice/new_edit', 'gmController@noticeNewEdit');//登录前公告编辑
        Route::any('notice/release_del', 'gmController@releaseDel');//发布和删除
        Route::any('query/basic', 'gmController@queryBasic');//玩家信息查询
        Route::any('query/hero', 'gmController@queryHero');//玩家英雄查询
        Route::any('query/money/flow', 'gmController@moneyFlow');//查询玩家货币流转记录
        Route::any('query/property/flow', 'gmController@propertyFlow');//查询玩家道具流转记录
        Route::any('query/property/flow', 'gmController@propertyFlow');//查询玩家道具流转记录
    });

    //其他
    Route::group(['prefix' => 'other'], function () {
        Route::any('user', 'otherController@user');//后台用户管理
        Route::any('user/add', 'otherController@userAdd');//后台用户添加管理
        Route::any('user/edit', 'otherController@userEdit');//后台用户修改管理
        Route::any('user/delete', 'otherController@userDelete');//后台用户删除管理

        Route::any('user/record', 'otherController@record');//后台用户操作记录
        Route::any('QRcode', 'otherController@QRcode');//生成二维码
        Route::any('scan', 'otherController@scan');//扫描
    });
});