@extends('common')

@include('nav.online')

@section('content')
    <style>
        .row {
            padding-bottom: 20px;
        }
    </style>
    <section class="Hui-article-box">
        <nav class="breadcrumb"><i class="Hui-iconfont"></i> <a href="/" class="maincolor">首页</a>
            <span class="c-999 en">&gt;</span>
            <span class="c-666">仪表盘</span>
            <a class="btn btn-success radius r" style="line-height:1.6em;margin-top:3px"
               href="javascript:location.replace(location.href);" title="刷新"><i class="Hui-iconfont">&#xe68f;</i></a>
        </nav>
        <div class="Hui-article">
            <article class="cl pd-20">
                <div class="row cl">
                    <div class="col-xs-10 col-sm-10 col-offset-2">
                        <p class="f-10">快速导航</p>
                    </div>
                    <div class="col-xs-10 col-sm-10 col-offset-2">
                        <div class="Hui-tags-has">
                            <span> <a href="/income/paytotal"> 收入 </a></span>
                            <span> <a href="/user/total"> 注册用户 </a></span>
                            <span> <a href="/online/maxaverage"> 在线用户 </a></span>
                            <span> <a href="/user/newkeep"> 新增用户次日留存 </a></span>
                            <span> <a href="/user/activekeep"> 活跃用户次日留存 </a></span>
                            <span> <a href="/income/paytotal"> 付费情况 </a></span>
                            <span> <a href="/income/paydetail"> 付费细节 </a></span>
                            <span> <a href="/income/payKTV"> LTV </a></span>
                        </div>
                    </div>
                </div>

                <div class="row cl">
                    <div class="ol-xs-10 col-sm-10 col-offset-2">
                        <p class="f-20 text-success">仪表盘</p>
                    </div>
                </div>

                <div class="row cl">
                    <div class="col-xs-2 col-sm-2 col-offset-2">
                        <a href="/?option-date=1" class="btn btn-secondary radius">
                            今日
                        </a>

                        <a href="/?option-date=2" class="btn btn-secondary radius">
                            本周
                        </a>

                        <a href="/?option-date=3" class="btn btn-secondary radius">
                            本月
                        </a>
                    </div>
                </div>

                <div class="row cl">
                    <div class="col-xs-2 col-sm-2 col-offset-2">
                        <div class="panel panel-primary box-shadow radius">
                            <div class="panel-header">SALES（收入）</div>
                            <div class="panel-body">
                                <h2>{{ $data['income'] }}</h2>
                                <div class="text-r"><a href="/income/paytotal"><i
                                                class="Hui-iconfont">&#xe665;</i>详情</a></div>
                            </div>
                        </div>
                    </div>
                    <div class="col-xs-2 col-sm-2">
                        <div class="panel panel-primary radius">
                            <div class="panel-header">NUU（新激活）</div>
                            <div class="panel-body">
                                <h2>{{ $data['user_create'] }}</h2>
                                <div class="text-r"><a href="/user/realtimertotal"><i
                                                class="Hui-iconfont">&#xe665;</i>详情</a></div>
                            </div>
                        </div>
                    </div>
                    <div class="col-xs-2 col-sm-2">
                        <div class="panel panel-primary box-shadow radius">
                            <div class="panel-header">AU（登陆）</div>
                            <div class="panel-body">
                                <h2>{{ $data['login_sum'] }}</h2>
                                <div class="text-r"><a href="/user/realtimertotal"><i
                                                class="Hui-iconfont">&#xe665;</i>详情</a>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-xs-2 col-sm-2">
                        <div class="panel panel-primary radius">
                            <div class="panel-header">在线人数</div>
                            <div class="panel-body">
                                <h2>{{ $data['max_online'] }}</h2>
                                <div class="text-r"><a href="/online/maxaverage"><i
                                                class="Hui-iconfont">&#xe665;</i>详情</a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row cl">
                    <div class="col-xs-2 col-sm-2 col-offset-2">
                        <div class="panel panel-primary box-shadow radius">
                            <div class="panel-header">PAYRATE（付费率）</div>
                            <div class="panel-body">
                                <h2>{{ $data['pay_rat'] }}</h2>
                                <div class="text-r"><a href="/income/timelypay"><i
                                                class="Hui-iconfont">&#xe665;</i>详情</a>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-xs-2 col-sm-2">
                        <div class="panel panel-primary box-shadow radius">
                            <div class="panel-header">PAYUU（付费人数）</div>
                            <div class="panel-body">
                                <h2>{{ $data['pay_user'] }}</h2>
                                <div class="text-r"><a href="/income/timelypay"><i
                                                class="Hui-iconfont">&#xe665;</i>详情</a>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-xs-2 col-sm-2">
                        <div class="panel panel-primary box-shadow radius">
                            <div class="panel-header">ARPU（人均消费）</div>
                            <div class="panel-body">
                                <h2>{{ $data['pay_login_rat'] }}</h2>
                                <div class="text-r"><a href="/income/timelypay"><i
                                                class="Hui-iconfont">&#xe665;</i>详情</a>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-xs-2 col-sm-2">
                        <div class="panel panel-primary radius">
                            <div class="panel-header">ARPPU（付费人均消费）</div>
                            <div class="panel-body">
                                <h2>{{ $data['pay_user_rat'] }}</h2>
                                <div class="text-r"><a href="/user/total"><i class="Hui-iconfont">&#xe665;</i>详情</a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row cl">
                    <div class="col-xs-2 col-sm-2 col-offset-2">
                        <div class="panel panel-primary radius">
                            <div class="panel-header">活跃人数</div>
                            <div class="panel-body">
                                <h2>{{ $data['active'] }}</h2>
                                <div class="text-r"><a href="/user/active"><i
                                                class="Hui-iconfont">&#xe665;</i>详情</a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </article>
        </div>
    </section>
@endsection