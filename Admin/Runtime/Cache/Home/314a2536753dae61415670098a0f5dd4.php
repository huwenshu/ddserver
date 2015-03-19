<?php if (!defined('THINK_PATH')) exit();?><!doctype html>
<html>
<head>
    <meta charset="UTF-8">
    <title><?php echo ($meta_title); ?></title>
    <script>
        /**
         * Created by Common on 14-9-12.
         */
        var deviceAgent = navigator.userAgent.toLowerCase();
        var agentID = deviceAgent.match(/(iphone|ipod|ipad|android)/) || '';
        var mobile = {
            iphone:agentID.indexOf("iphone")>=0
            ,android:agentID.indexOf("android")>=0
            ,ipad:agentID.indexOf("ipad")>=0
            ,ipad:agentID.indexOf("ipod")>=0
        }
        if(mobile.iphone){
            //alert('iphone');
            document.write('<meta name="viewport" content="width=device-width, initial-scale=01, user-scalable=0,minimal-ui">');
        }else if(mobile.android){
            //alert('android');
            document.write('<meta name="viewport" content="width=device-width, initial-scale=1, user-scalable=0">');
            //document.write('<meta name="viewport" content="target-densitydpi=device-dpi">');
        }else{
            //alert('other');
            document.write('<meta name="viewport" content="width=device-width, initial-scale=0.5, user-scalable=0,minimal-ui">');
        }
    </script>
    <link href="/ddserver/Public/favicon.ico" type="image/x-icon" rel="shortcut icon">
    <link rel="stylesheet" type="text/css" href="/ddserver/Public/Home/css/base.css" media="all">
    <link rel="stylesheet" type="text/css" href="/ddserver/Public/Home/css/common.css" media="all">
    <link rel="stylesheet" type="text/css" href="/ddserver/Public/Home/css/module.css">
    <link rel="stylesheet" type="text/css" href="/ddserver/Public/Home/css/style.css" media="all">
    <link rel="stylesheet" type="text/css" href="/ddserver/Public/Home/css/mui.min.css" media="all">
    <link rel="stylesheet" type="text/css" href="/ddserver/Public/Home/css/dudu.sales.css" media="all">
     <!--[if lt IE 9]>
    <script type="text/javascript" src="/ddserver/Public/static/jquery-1.10.2.min.js"></script>
    <![endif]--><!--[if gte IE 9]><!-->
    <script type="text/javascript" src="/ddserver/Public/static/jquery-2.0.3.min.js"></script>
    <script type="text/javascript" src="/ddserver/Public/static/jquery-ui.min.js"></script>
    <script type="text/javascript" src="/ddserver/Public/Home/js/jquery.mousewheel.js"></script>
    <!--<![endif]-->
    
    <style>
        body{padding: 0}
        .myborder{
        }
        #panel-2 .myborder input,#panel-2 .myborder select,#panel-1 .myborder input,#panel-1 .myborder select{
            width: 65%;
        }
        #panel-1 .myborder textarea,#panel-2 .myborder textarea{
            height: 100px;
        }
        .ui-tabs-nav{
            position: relative;
            display: table;
            width: 100%;
            overflow: hidden;
            font-size: 15px;
            font-weight: 400;
            table-layout: fixed;
            background-color: transparent;
            border: 1px solid #383838;
            border-radius: 3px;
        }
        .ui-tabs-nav>li{
            display: table-cell;
            width: 1%;
            padding-top: 6px;
            padding-bottom: 7px;
            overflow: hidden;
            line-height: 1;
            color: #007aff;
            text-align: center;
            text-overflow: ellipsis;
            white-space: nowrap;
            border-color: #383838;
            border-left: 1px solid #007aff;
            -webkit-transition: background-color .1s linear;
            transition: background-color .1s linear;
        }
        .ui-tabs-nav>li.ui-tabs-active{
            color: #fff;
            background-color: #383838;
        }
        .ui-tabs-nav>li>a{
            color: #007aff;
        }
        .ui-tabs-nav>li.ui-tabs-active>a{
            color:#fff;
            text-decoration: underline;
        }
        form button{

        }
        #panel-2 input{
            width: 95%;
        }


    </style>

</head>
<body>
    <!-- 头部 -->
    <div class="header">
        <!-- Logo -->
        <span style="color:white;margin-left:40px;font-size:25px;"><a href="<?php echo U('Index/index');?>">嘟嘟停车</a></span>
            <!-- /Logo -->
        <!-- /Logo -->

        <!-- 主导航 -->
        <ul class="main-nav">
            <?php if(is_array($__MENU__["main"])): $i = 0; $__LIST__ = $__MENU__["main"];if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$menu): $mod = ($i % 2 );++$i;?><li class="<?php echo ((isset($menu["class"]) && ($menu["class"] !== ""))?($menu["class"]):''); ?>"><a href="<?php echo (U($menu["url"])); ?>"><?php echo ($menu["title"]); ?></a></li><?php endforeach; endif; else: echo "" ;endif; ?>
        </ul>
        <!-- /主导航 -->

        <!-- 用户栏 -->
        <div class="user-bar">
            <a href="javascript:;" class="user-entrance"><i class="icon-user"></i></a>
            <ul class="nav-list user-menu hidden">
                <li class="manager"><em title="<?php echo session('admin_auth.username');?>"><?php echo session('admin_auth.username');?></em></li>
                <li><a href="<?php echo U('Public/logout');?>">退出</a></li>
            </ul>
        </div>
    </div>
    <!-- /头部 -->

    <!-- 边栏 -->
    <!-- <div class="sidebar">
       <!-- 子导航 -->
        
            <div id="subnav" class="subnav">
                <?php if(!empty($_extra_menu)): ?>
                    <?php echo extra_menu($_extra_menu,$__MENU__); endif; ?>
                <?php if(is_array($__MENU__["child"])): $i = 0; $__LIST__ = $__MENU__["child"];if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$sub_menu): $mod = ($i % 2 );++$i;?><!-- 子导航 -->
                    <?php if(!empty($sub_menu)): if(!empty($key)): ?><h3><i class="icon icon-unfold"></i><?php echo ($key); ?></h3><?php endif; ?>
                        <ul class="side-sub-menu">
                            <?php if(is_array($sub_menu)): $i = 0; $__LIST__ = $sub_menu;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$menu): $mod = ($i % 2 );++$i;?><li>
                                    <a class="item" href="<?php echo (U($menu["url"])); ?>"><?php echo ($menu["title"]); ?></a>
                                </li><?php endforeach; endif; else: echo "" ;endif; ?>
                        </ul><?php endif; ?>
                    <!-- /子导航 --><?php endforeach; endif; else: echo "" ;endif; ?>
            </div>
        
        <!-- /子导航 -->
    </div>-->
   <!-- /边栏 -->

    <!-- 内容区 -->
    <div id="main-content">
        <div id="top-alert" class="fixed alert alert-error" style="display: none;">
            <button class="close fixed" style="margin-top: 4px;">&times;</button>
            <div class="alert-content">这是内容</div>
        </div>
        <div id="main" class="main">
            
            <!-- nav -->
            <?php if(!empty($_show_nav)): ?><div class="breadcrumb">
                <span>您的位置:</span>
                <?php $i = '1'; ?>
                <?php if(is_array($_nav)): foreach($_nav as $k=>$v): if($i == count($_nav)): ?><span><?php echo ($v); ?></span>
                    <?php else: ?>
                    <span><a href="<?php echo ($k); ?>"><?php echo ($v); ?></a>&gt;</span><?php endif; ?>
                    <?php $i = $i+1; endforeach; endif; ?>
            </div><?php endif; ?>
            <!-- nav -->
            

            
<!-- 主体 -->
<div id="indexMain" class="index-main" style="padding-top: 35px">
    <form action="<?php echo U('giftInfo');?>" method="post" enctype="multipart/form-data">
        <input type="hidden"  name="id" value="<?php echo ($giftInfo[id]); ?>" />
        <table style="display: flex;width: 95%;margin: 0 auto;margin-top: 10px">
            <tr>
                <td style="width: 33%"><label for="giftname">礼品名称：</label></td>
                <td style="width: 66%"><input type="text" id="giftname" name="name" value="<?php echo ($giftInfo[name]); ?>" /> </td>
            </tr>
            <tr>
                <td><label for="giftscore">礼品积分：</label></td>
                <td><input type="text" id="giftscore" name="score" value= "<?php echo ($giftInfo[score]); ?>" /> </td>
            </tr>
            <tr>
                <td><label for="giftpic">礼品图片：</label></td>
                <td><input type="file" id="giftpic" name="pic" value= "" /><span style="color:red"><?php echo ($fileError); ?></span> </td>
            </tr>
            <tr>
                <td><label for="giftvalid">是否显示：</label></td>
                <td>
                <select id="giftvalid" name="valid">
                    <option value="0"  <?php if ($giftInfo[valid]==0) echo 'selected="selected"';?>>否</option>
                    <option value="1"  <?php if ($giftInfo[valid]==1) echo 'selected="selected"';?>>是</option>
                </select>
                </td>
            </tr>
            <tr>
                <td><label for="giftweight">权重：</label></td>
                <td><input type="text" id="giftweight" name="weight" value= "<?php echo ($giftInfo[weight]); ?>" /> </td>
            </tr>
            <tr>
                <td>
                    礼品图片：
                </td>
                <td>
                    <img src="<?php echo ($imageRoot); ?>/<?php echo ($giftInfo[image]); ?>">
                </td>
            </tr>
            <tr>
                <td colspan=2>
                    <input type="submit" />
                </td>
            </tr>
        </table>
    </form>

</div>

        </div>
        <div class="cont-ft">
            <div class="copyright">
                <div class="fl"></div>
                <div class="fr"></div>
            </div>
        </div>
    </div>
    <!-- /内容区 -->
    <script type="text/javascript">
    (function(){
        var ThinkPHP = window.Think = {
            "ROOT"   : "/ddserver", //当前网站地址
            "APP"    : "/ddserver/admin.php", //当前项目地址
            "PUBLIC" : "/ddserver/Public", //项目公共目录地址
            "DEEP"   : "<?php echo C('URL_PATHINFO_DEPR');?>", //PATHINFO分割符
            "MODEL"  : ["<?php echo C('URL_MODEL');?>", "<?php echo C('URL_CASE_INSENSITIVE');?>", "<?php echo C('URL_HTML_SUFFIX');?>"],
            "VAR"    : ["<?php echo C('VAR_MODULE');?>", "<?php echo C('VAR_CONTROLLER');?>", "<?php echo C('VAR_ACTION');?>"]
        }
    })();
    </script>
    <script type="text/javascript" src="/ddserver/Public/static/think.js"></script>
    <script type="text/javascript" src="/ddserver/Public/Home/js/common.js"></script>
    <script type="text/javascript">
        +function(){
            var $window = $(window), $subnav = $("#subnav"), url;
            $window.resize(function(){
                $("#main").css("min-height", $window.height() - 130);
            }).resize();

            /* 左边菜单高亮 */
            url = window.location.pathname + window.location.search;
            url = url.replace(/(\/(p)\/\d+)|(&p=\d+)|(\/(id)\/\d+)|(&id=\d+)|(\/(group)\/\d+)|(&group=\d+)/, "");
            $subnav.find("a[href='" + url + "']").parent().addClass("current");

            /* 左边菜单显示收起 */
            $("#subnav").on("click", "h3", function(){
                var $this = $(this);
                $this.find(".icon").toggleClass("icon-fold");
                $this.next().slideToggle("fast").siblings(".side-sub-menu:visible").
                      prev("h3").find("i").addClass("icon-fold").end().end().hide();
            });

            $("#subnav h3 a").click(function(e){e.stopPropagation()});

            /* 头部管理员菜单 */
            $(".user-bar").mouseenter(function(){
                var userMenu = $(this).children(".user-menu ");
                userMenu.removeClass("hidden");
                clearTimeout(userMenu.data("timeout"));
            }).mouseleave(function(){
                var userMenu = $(this).children(".user-menu");
                userMenu.data("timeout") && clearTimeout(userMenu.data("timeout"));
                userMenu.data("timeout", setTimeout(function(){userMenu.addClass("hidden")}, 100));
            });

	        /* 表单获取焦点变色 */
	        $("form").on("focus", "input", function(){
		        $(this).addClass('focus');
	        }).on("blur","input",function(){
				        $(this).removeClass('focus');
			        });
		    $("form").on("focus", "textarea", function(){
			    $(this).closest('label').addClass('focus');
		    }).on("blur","textarea",function(){
			    $(this).closest('label').removeClass('focus');
		    });

            // 导航栏超出窗口高度后的模拟滚动条
            var sHeight = $(".sidebar").height();
            var subHeight  = $(".subnav").height();
            var diff = subHeight - sHeight; //250
            var sub = $(".subnav");
            if(diff > 0){
                $(window).mousewheel(function(event, delta){
                    if(delta>0){
                        if(parseInt(sub.css('marginTop'))>-10){
                            sub.css('marginTop','0px');
                        }else{
                            sub.css('marginTop','+='+10);
                        }
                    }else{
                        if(parseInt(sub.css('marginTop'))<'-'+(diff-10)){
                            sub.css('marginTop','-'+(diff-10));
                        }else{
                            sub.css('marginTop','-='+10);
                        }
                    }
                });
            }
        }();
    </script>
    
<!--<script src="http://webapi.amap.com/maps?v=1.3&key=5b768e7a9bc53cf2286ba6122e599bb4" type="text/javascript"></script>-->
<script type="text/javascript" src="/ddserver/Public/static/iscroll.js"></script>


</body>
</html>