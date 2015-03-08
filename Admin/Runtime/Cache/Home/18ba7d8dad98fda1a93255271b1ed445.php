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
        <div class="tabs">
        <ul>
            <li><a href="#panel-1">基本资料</a></li>
            <li><a href="#panel-2">拜访记录</a></li>
        </ul>
        <div id="panel-1">
            <form action="<?php echo U('parkinfo');?>" method="post">
                <input type="hidden"  name="id" value="<?php echo ($park_info[id]); ?>" />
                <table style="display: flex;width: 95%;margin: 0 auto;margin-top: 10px">
                    <tr>
                        <td style="width: 33%"><label for="parkname">停车场名称：</label></td>
                        <td style="width: 66%"><input type="text" id="parkname" name="name" value="<?php echo ($park_info[name]); ?>" /> </td>
                    </tr>
                    <tr>
                        <td><label for="enterplace">入口地址：</label></td>
                        <td><input type="text" id="enterplace" name="address" value= "<?php echo ($park_info[address]); ?>" /> </td>
                    </tr>
                    <tr>
                        <td><label for="otherplace">其它入口地址：</label></td>
                        <td><input type="text" id="otherplace" name="address2" value= "<?php echo ($park_info[address2]); ?>" /> </td>
                    </tr>
                    <tr>
                        <td><label>导航坐标：</label></td>
                        <td>
                            <table style="width: 100%" border="0">
                                <tr>
                                    <td>经度</td>
                                    <td>纬度</td>
                                </tr>
                                <tr>
                                    <td><input style="width: 100%" type="text" readonly  name="lat" value= "<?php echo ($park_info[lat]); ?>" /></td>
                                    <td><input style="width: 100%" type="text" readonly  name="lng" value= "<?php echo ($park_info[lng]); ?>" /></td>
                                </tr>
                                <tr>
                                    <td colspan="2">
                                        <div id="btgetloc" class="mui-btn mui-btn-warning mui-btn-block">获取坐标</div>
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>
                    <tr>
                        <td colspan="2"><label for="parkstyle">停车场类型：</label></td>
                        <!--<td> </td>-->
                    </tr>
                    <tr>
                        <td colspan="2">
                            <?php
 $parkstyles = C('PARK_STYLE'); $mystyle = $park_info[style]; foreach($parkstyles as $parkstyle => $value){ $token = '|'.$parkstyle.'|'; $checked = ""; if (preg_match($token,$mystyle)) { $checked = "checked"; } echo '<input type="checkbox" name="parkstyle[]" value="'.$parkstyle.'" '.$checked.' /><span style="display: inline-block;min-width: 40%">'.$value.'</span>'; } ?>
                        </td>
                    </tr>
                    <tr>
                        <td colspan="2"><br></td>
                    </tr>
                    <tr>
                        <td><label for="spacesum">总车位数：</label></td>
                        <td><input type="text" id="spacesum" name="spacesum" value= "<?php echo ($park_info[spacesum]); ?>" /> </td>
                    </tr>
                    <tr>
                        <td><label for="opentime">开放时间段：</label></td>
                        <td>
                        	<input type="text" id="opentime" name="opentime" value= "<?php echo ($park_info[opentime]); ?>" /> <br>
                        	周一：<input type="text" id="startmon" name="startmon" value= "<?php echo ($park_info[startmon]); ?>" style="width: 110px;"/> - <input type="text" id="endmon" name="endmon" value= "<?php echo ($park_info[endmon]); ?>" style="width: 110px;"/><br>
                        	周二：<input type="text" id="starttue" name="starttue" value= "<?php echo ($park_info[starttue]); ?>" style="width: 110px;"/> - <input type="text" id="endtue" name="endtue" value= "<?php echo ($park_info[endtue]); ?>" style="width: 110px;"/><br>
                        	周三：<input type="text" id="startwed" name="startwed" value= "<?php echo ($park_info[startwed]); ?>" style="width: 110px;"/> - <input type="text" id="endwed" name="endwed" value= "<?php echo ($park_info[endwed]); ?>" style="width: 110px;"/><br>
                        	周四：<input type="text" id="startthu" name="startthu" value= "<?php echo ($park_info[startthu]); ?>" style="width: 110px;"/> - <input type="text" id="endthu" name="endthu" value= "<?php echo ($park_info[endthu]); ?>" style="width: 110px;"/><br>
                        	周五：<input type="text" id="startfri" name="startfri" value= "<?php echo ($park_info[startfri]); ?>" style="width: 110px;"/> - <input type="text" id="endfri" name="endfri" value= "<?php echo ($park_info[endfri]); ?>" style="width: 110px;"/><br>
                        	周六：<input type="text" id="startsat" name="startsat" value= "<?php echo ($park_info[startsat]); ?>" style="width: 110px;"/> - <input type="text" id="endsat" name="endsat" value= "<?php echo ($park_info[endsat]); ?>" style="width: 110px;"/><br>
                        	周日：<input type="text" id="startsun" name="startsun" value= "<?php echo ($park_info[startsun]); ?>" style="width: 110px;"/> - <input type="text" id="endsun" name="endsun" value= "<?php echo ($park_info[endsun]); ?>" style="width: 110px;"/>
                        </td>
                    </tr>
                    <tr>
                        <td colspan="2"><label for="chargingrules">计费规则：</label><a href="<?php echo ($feeurl); ?>" target="newwindow">规则库中共<?php echo ($rulecount); ?>条纪录</a></td>
                    </tr>
                    <tr>
                        <td colspan="2">
                            <textarea id="chargingrules" name="chargingrules" rows="5"><?php echo ($park_info[chargingrules]); ?></textarea>
                        </td>
                    </tr>
                    <tr>
                        <td colspan="2"><label for="note">备注说明：</label></td>
                        <!--<td><input type="text" id="note" name="note" value= "<?php echo ($park_info[note]); ?>" /> </td>-->
                    </tr>
                    <tr>
                        <td colspan="2">
                            <textarea id="note" name="note" rows="5"><?php echo ($park_info[note]); ?></textarea>
                        </td>
                    </tr>
                    <tr>
                        <td><label for="shortname">停车场缩写：</label></td>
                        <td><input type="text" id="shortname" name="shortname" value="<?php echo ($park_info[shortname]); ?>" /> </td>
                    </tr>

                    <tr>
                        <td colspan=2>
                            <input type="submit" />
                        </td>
                    </tr>
                </table>
            </form>

        </div>
        <div id="panel-2">
                <table style="display: flex;width: 95%;margin: 0 auto;margin-top: 10px">
                    <tr>
                        <td>
                            <fieldset>
                                <legend>合作状态:</legend>
                                <form action="<?php echo U('savecorp');?>" method="post">
                                    <input type="hidden"  name="id" value="<?php echo ($park_info[id]); ?>" />
                                    <select id="status" name="status">
                                        <option value="1"  <?php if ($park_info[status]==1) echo 'selected="selected"';?>>已合作</option>
                                        <option value="2"  <?php if ($park_info[status]==2) echo 'selected="selected"';?>>测试中</option>
                                        <option value="0"  <?php if ($park_info[status]==0) echo 'selected="selected"';?>>正在洽谈</option>
                                        <option value="-1" <?php if ($park_info[status]==-1) echo 'selected="selected"';?>>未接触</option>

                                    </select>
                                    <input type="submit" class="mui-btn mui-btn-success mui-btn-blue" style="margin-top: 1px" value="保存合作状态"/>
                                </form>
                        </td>
                    </tr>


                    <tr>
                        <td>
                            <fieldset>
                                <legend>拜访记录:</legend>
                            <ul style="width: 100%">

                                <?php
 $visits = $park_info[Visit]; foreach ($visits as $key => $value) { echo "<li class='myborder' style='overflow: hidden'>"; echo "<form action=".U('savevisit')." method=post>"; echo "<input type='hidden'  name='parkid' value=$park_info[id] />"; echo "<input type='hidden'  name='id' value=$value[id] />"; echo "拜访时间：<input style='width: 50%' type='text'  name='visitime' value='".$value['visitime']."'/><br>"; echo " 合作意愿：<select style='width: 50%'  name='intention'><option value='0'".intention(0,$value["intention"]).">明确拒绝</option><nobr>"; echo "<option value='1'".intention(1,$value["intention"]).">很弱</option>"; echo "<option value='2'".intention(2,$value["intention"]).">一般</option>"; echo "<option value='3'".intention(3,$value["intention"]).">强</option>"; echo "</select> <br>"; echo "备注：<br><textarea   name='note' value='' />".$value['note']."</textarea>"; echo "<button style='float: left;margin: 10px;' type='button' class='saveVisit mui-btn mui-btn-danger'>保存</button>"; echo " </form>"; echo "<form action=".U('delvisit')." method=post>"; echo "<input type='hidden'  name='parkid' value=$park_info[id] />"; echo "<input type='hidden'  name='id' value=$value[id] />"; echo "<button style='float: right;margin: 10px;' type='button' class='removeVisit mui-btn mui-btn-danger'>删除</button><hr style='clear: both;margin: 5px'></li>"; echo " </form>"; } function intention($value1,$value2){ if($value1 == $value2){ return " selected='selected'"; } else return ""; } ?>

                            </ul>
                            <button class="mui-btn mui-btn-success mui-btn-block" id="addVisit">添加拜访记录</button>
                            </fieldset>
                        </td>
                    </tr><tr>
                    <td>
                        <fieldset>
                            <legend>合作联系人:</legend>
                        <ul>
                            <?php
 $contacts = $park_info[Contact]; foreach ($contacts as $key => $value) { echo "<li class='myborder'>"; echo "<form action=".U('savecontact')." method=post>"; echo "<input type='hidden'  name='parkid' value=$park_info[id] />"; echo "<input type='hidden'  name='id' value=$value[id] />"; echo "姓名：<input type='text'  name='contactname' value='".$value['name']."'/><br>"; echo "性别：<select  name='contactgender'><option value='0'".gender('0',$value["gender"]).">男</option><option value='1'".gender('1',$value["gender"]).">女</option></select><br>"; echo "电话：<input type='text'  name='contactphone' value='".$value['telephone']."' /><br>"; echo "职务：<input type='text'  name='contactjob' value='".$value['job']."' /><br>"; echo "<button style='float: left;margin: 10px;' type='button' class='saveContact mui-btn mui-btn-danger'>保存</button>"; echo " </form>"; echo "<form action=".U('delcontact')." method=post>"; echo "<input type='hidden'  name='parkid' value=$park_info[id] />"; echo "<input type='hidden'  name='id' value=$value[id] />"; echo "<button style='float: right;margin: 10px' type='button' class='removeContact mui-btn mui-btn-danger'>删除</button><hr style='clear: both;margin: 5px'></li>"; echo " </form>"; } function gender($value1,$value2){ if($value1 == $value2){ return " selected='selected'"; } else return ""; } ?>
                        </ul>
                        <button type="button" class="mui-btn mui-btn-success mui-btn-block" id="addContact">添加联系人</button>
                        </fieldset>
                    </td>
                    </tr><tr>
                    <td>
                        <fieldset>
                            <legend>车场管理员:</legend>
                            <ul>
                                <?php
 $admins = $park_info[Admin]; foreach ($admins as $key => $value) { echo "<li class='myborder'>"; echo "<form action=".U('saveadmin')." method=post>"; echo "<input type='hidden'  name='parkid' value=$park_info[id] />"; echo "<input type='hidden'  name='id' value=$value[id] />"; echo "停车场：<input type='text'  name='parkname' readonly value='".$value['parkname']."'/><br>"; echo "用户名：<input type='text'  name='username' value='".$value['username']."'/><br>"; echo "密码：<input type='password'  name='password' value='' /><br>"; echo "姓名：<input type='text'  name='name' value='".$value['name']."' /><br>"; echo "电话：<input type='text'  name='phone' value='".$value['phone']."' /><br>"; echo "职能:<br>".getJobs($value['jobfunction']); echo "<br>"; echo "<button style='float: left;margin: 10px;' type='button' class='saveAdmin mui-btn mui-btn-danger'>保存</button>"; echo " </form>"; echo "<form action=".U('deladmin')." method=post>"; echo "<input type='hidden'  name='parkid' value=$park_info[id] />"; echo "<input type='hidden'  name='id' value=$value[id] />"; echo "<button style='float: right;margin: 10px' type='button' class='removeAdmin mui-btn mui-btn-danger'>删除</button><hr style='clear: both;margin: 5px'></li>"; echo " </form>"; } function getJobs($jobs){ $tmp = ''; if(($jobs & 1) == 1){ $tmp .= '<input style="width: 10%" type="checkbox" name="jobfunction[]" value="1" checked /><span>入口管理</span>'; } else{ $tmp .= '<input style="width: 10%" type="checkbox" name="jobfunction[]" value="1" /><span>入口管理</span>'; } if(($jobs & 2) == 2){ $tmp .= '<input style="width: 10%" type="checkbox" name="jobfunction[]" value="2" checked /><span>出口管理</span>'; } else{ $tmp .= '<input style="width: 10%" type="checkbox" name="jobfunction[]" value="2" /><span>出口管理</span>'; } if(($jobs & 4) == 4){ $tmp .= '<input style="width: 10%" type="checkbox" name="jobfunction[]" value="4" checked /><span>收费</span><br>'; } else{ $tmp .= '<input style="width: 10%" type="checkbox" name="jobfunction[]" value="4" /><span>收费</span><br>'; } if(($jobs & 8) == 8){ $tmp .= '<input style="width: 10%" type="checkbox" name="jobfunction[]" value="8" checked /><span>公司管理</span>'; } else{ $tmp .= '<input style="width: 10%" type="checkbox" name="jobfunction[]" value="8" /><span>公司管理</span>'; } if(($jobs & 16) == 16){ $tmp .= '<input style="width: 10%" type="checkbox" name="jobfunction[]" value="16" checked /><span>金额提现</span>'; } else{ $tmp .= '<input style="width: 10%" type="checkbox" name="jobfunction[]" value="16" /><span>金额提现</span>'; } return $tmp; } ?>
                            </ul>
                            <button type="button" class="mui-btn mui-btn-success mui-btn-block" id="addAdmin">添加管理员</button>
                        </fieldset>
                    </td>

                </table>

        </div>
        </div>

      
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
    <script>
    (function($){
            $.fn.Maplocation = (function(){
                var mapcontaion = $('<div></div>');
                var mapmake = $('<div></div>');
                var mapdom = $('<div></div>');
                var mapoppanel = $('<div>' +
                        '<div name="info" style="clear: both;padding: 10px;border-bottom: solid 1px #ccc;position: absolute;left: 0;bottom: 100%;z-index: 10000;background-color: rgba(0,0,0,.8);width: 100%;color: #fff">' +
                        '<table style="width: 100%"><tr>' +
                        '<td>经度:<span name="lat"></span></td>' +
                        '<td>纬度:<span name="lng"></span></td>' +
                        '</tr>' +
                        '<tr>' +
                        '<td><div name="btclose" style="text-align: center;border: solid 1px #ccc;margin: 5%;">退出</div></td>' +
                        '<td><div name="btselect" style="text-align: center;border: solid 1px #ccc;margin: 5%;">选择</div></td>' +
                        '</tr>' +
                        '</table>' +
                        '</div>' +
                        '<div name="list" style="width: 100%;height: 100%;overflow: hidden;position: absolute;left: 0;top: 0;background-color: #fff"><ul style="list-style: none;margin: 0;padding: 0"></ul></div>' +
                        '</div>');
                var mapid = '__________mapid';

                mapdom.attr('id',mapid);
                mapcontaion.append(mapmake).append(mapdom).append(mapoppanel);
                mapcontaion.css({
                    position:'fixed'
                    ,display:'none'
                    ,width:'100%'
                    ,height:'100%'
                    ,'zIndex':'10000'
                    ,left:0
                    ,top:0
                    ,backgroundColor:'rgba(0,0,0,.5)'
                });
                mapmake.css({
                    position:'absolute'
                    ,width:'100%'
                    ,height:'100%'
                }).click(function(){
                    mapcontaion.hide();
                });
                mapdom.css({
                   position:'absolute'
                    ,width:'100%'
                    ,height:'60%'
                    ,left:0
                    ,top:0

                });
                mapoppanel.css({
                    position:'absolute'
                    ,width:'100%'
                    ,height:'40%'
                    ,left:0
                    ,top:'60%'
                    ,backgroundColor:'#fff'
                }).find('[name=btclose]').click(function(){
                   obj.close();
                }).end().find('[name=btselect]').click(function(){
                   obj.select();
                });
                $(document.body).append(mapcontaion);

                var mapcontaionui = {
                    iscroll:null
                    ,show:function(){
                        mapcontaion.show();
                    }
                    ,hide:function(){
                        mapcontaion.hide();
                    }
                    ,list:function(datas, cb){
                        var list = mapcontaion.find('[name=list]');
                        var innerlist = list.find('>ul');
                        innerlist.empty();

                        for(var i = 0;i<datas.length;i++){
                            var data = datas[i];
                            var row = $('<li style="padding-top: 10px;padding-bottom: 10px"></li>');
                            (function(row,data){
                                row.html(data.formattedAddress);
                                row.click(function(){
                                    console.log(data);
                                    cb && cb(data);
                                })
                            })(row,data);
                            innerlist.append(row);
                        }
                        if(datas.length>0){
                            list.show();
                        }else{
                            list.hide();
                        }
                        if(!this.iscroll){
                            this.iscroll = new iScroll(innerlist[0], {desktopCompatibility:true});
                        }else{
                            this.iscroll.refresh();
                        }
                    }
                    ,setInfo:function(position){
                        var lat = mapcontaion.find('[name=lat]');
                        var lng = mapcontaion.find('[name=lng]');
                        lat.html(position.lat);
                        lng.html(position.lng);
                    }
                }
                var loadmapscript = (function(){
                   var callback = null;
                    var isloading = false;
                   var initname = 'loadmapscriptinit_'+(new Date()-0);
                   window[initname] = function(){
                       isloading = true;
                        //alert('initname'+'\n'+'loadmap');
                       callback && callback();
                   }


                   var obj = {
                       load:function(_callback){
                           if(isloading){
                               callback && callback();
                           }else{
                               callback = _callback;
                               var script = document.createElement("script");
                               script.type = "text/javascript";
                               script.src = "http://webapi.amap.com/maps?v=1.3&key=bc59f27d65900532cc4f3c1048dd6122&callback="+initname;
                               document.body.appendChild(script);
                           }
                       }
                   };
                   return obj;
                })();

                var obj = {
                    mapobj:null
                    ,onclose:null
                    ,init:function(callback, addressdom){
                        var me = this;
                        //var a = addressdom?addressdom.val():null;

                        loadmapscript.load(function(){
                            me.loadPosition(addressdom?addressdom.val():null, function(geocoderresult){
                                me.initMap(callback, geocoderresult);
                            });
                        });
                    }
                    ,initMap:function(callback,geocoderresult){
                        mapcontaion.show();
                        this.onclose = callback || null;
                        //var position=new AMap.LngLat(116.397428,39.90923);
                        var centermaker = null;
                        var mapObj = this.mapobj = new AMap.Map(mapid,{
                          view: new AMap.View2D({
                              //center:position,//创建中心点坐标
                            zoom:19, //设置地图缩放级别      //1 -  19
                           rotation:0 //设置地图旋转角度
                         }),
                         lang:"zh_cn"//设置地图语言类型，默认：中文简体
                        });//创建地图实例
                        mapObj.plugin(["AMap.ToolBar","AMap.OverView","AMap.Scale"],function(){
                          //加载工具条
                          var tool = new AMap.ToolBar({
                            direction:false,//隐藏方向导航
                            ruler:false,//隐藏视野级别控制尺
                             offset:new AMap.Pixel(10,200)
                            //autoPosition:true//自动定位
                          });
                          mapObj.addControl(tool);
                          //加载鹰眼
                          var view = new AMap.OverView();
                          mapObj.addControl(view);
                          //加载比例尺
                          //var scale = new AMap.Scale();mapObj.addControl(scale);
                        });

                        if(geocoderresult){
                            mapcontaionui.list(geocoderresult.geocodes, function(data){
                                mapObj.panTo(data.location);
                                setTimeout(function(){
                                    setposition();
                                },500);
                            });
                        }
                        AMap.event.addListener(mapObj,'complete',function(){
                             centermaker = new AMap.Marker({
                                map:mapObj
                                ,content:"<div style='width: 50px;height: 50px;border-radius: 25px;background-color: rgba(0,0,0,.2)'><div style='position: absolute;left: 50%;top:50%;width: 6px;height: 6px;border-radius: 3px;margin-left: -3px;margin-top: -3px;background-color:red'></div></div>"
                                ,position:mapObj.getCenter()
                                 ,offset:new AMap.Pixel(-25,-25)
                            });
                        });


                        AMap.event.addListener(mapObj,'moveend',function(){
                            setposition();
                        });
                        AMap.event.addListener(mapObj,'mapmove',function(){
                            setposition();
                        });
                        function setposition(){
                            centermaker.setPosition(mapObj.getCenter());
                            mapcontaionui.setInfo(mapObj.getCenter());
                        }
                        /**
                        navigator.geolocation.getCurrentPosition(function(a){
                            console.log(a)
                            //alert('当前支持获取位置');
                        }, function(e){
                            console.log('error',e);
                            alert('当前不支持获取地理位置');
                        })
                         */
                    }
                    ,loadPosition:function(address, cb){
                        if(address){
                            var MGeocoder;
                            //加载地理编码插件
                            AMap.service(["AMap.Geocoder"], function() {
                                MGeocoder = new AMap.Geocoder({
                                    //city:"010", //城市，默认：“全国”
                                    radius:1000 //范围，默认：500
                                });
                                //返回地理编码结果
                                //地理编码
                                MGeocoder.getLocation(address, function(status, result){
                                    if(status === 'complete' && result.info === 'OK'){
                                        cb && cb(result);
                                    }
                                });
                            });
                        }else{
                            cb && cb(null);
                        }
                    }
                    ,close:function(){
                        this.mapobj.destroy();
                        this.mapobj = null;
                        this.onclose = null;
                        mapcontaion.hide();
                    }
                    ,select:function(){
                        this.onclose && this.onclose(this.mapobj.getCenter());
                        this.close();
                    }
                }
                return function(callback, addressdom){
                    $(this).click(function(){
                        obj.init(callback, addressdom);
                    });
                }
            })();
        })(jQuery);
    </script>
<script type="text/javascript">
    $(function(){
        $('#btgetloc').Maplocation(function(position){
                console.log(position);
                $('[name=lng]').val(position.lng);
                $('[name=lat]').val(position.lat);
            },$('#enterplace'));
        $(".tabs").tabs();
        $('#addContact').click(function(){
                //var temp = "<li class='myborder'>姓名：<input type='text'  name='contactname[]' /> 性别：<select  name='contactgender[]'><option value='1'>男</option><option value='0' selected='selected'>女</option></select> 电话：<input type='text'  name='contactphone[]' value='' /> 职务：<input type='text'  name='contactjob[]' value='' /> <button type='button' class='removeContact'>删除</button></li>";

                    var temp = '<li class="myborder"><form action="<?php echo U('savecontact');?>" method=post><input type="hidden"  name="parkid" value="<?php echo ($park_info[id]); ?>" /> <input type="hidden"  name="id" value="" />姓名：<input type="text" name="contactname" value=""><br>性别：<select name="contactgender"><option value="1" selected="selected">男</option><option value="0">女</option></select><br>电话：<input type="text" name="contactphone" value=""><br>职务：<input type="text" name="contactjob" value=""><br>'
                    +  '<button style="float: left;margin: 10px;" type="button" class="saveContact mui-btn mui-btn-danger">保存</button>'+
                    '</form>'+  '<form action="<?php echo U('delcontact');?>" method=post>'+
                    '<input type="hidden"  name="parkid" value="<?php echo ($park_info[id]); ?>" /> <input type="hidden"  name="id" value="" />'+
                    '<button style="float: right;margin: 10px" type="button" class="removeContact mui-btn mui-btn-danger">删除</button><hr style="clear: both;margin: 5px"></li>'+'</form>';
                    var row = $(temp);
                row.find('.removeContact').click(function(){
                    //$(this).parent().remove();
                    $(this).parent().submit();
                });row.find('.saveContact').click(function(){
                        $(this).parent().submit();
                    });
                $(this).prev().append(row);
            }
        );
        
        $('.removeContact').click(function(){
                    //$(this).parent().remove();
                    $(this).parent().submit();
            }
        );
        $('.saveContact').click(function(){
                    $(this).parent().submit();
                }
        );
        $('#addVisit').click(function(){
                    //var tmp = "<li class='myborder'>拜访时间：<input type='text'  name='visitime[]' /> 合作意愿：<select  name='intention[]'><option value='0'>明确拒绝</option><option value='1'>很弱</option><option value='2'>一般</option><option value='3'>强</option></select> 备注：<input type='text'  name='note[]' value='' /> <button type='button' class='removeVisit'>删除</button></li>";
                    var tmp = '<li class="myborder"> <form action="<?php echo U('savevisit');?>" method=post><input type="hidden"  name="parkid" value="<?php echo ($park_info[id]); ?>" /> <input type="hidden"  name="id" value="" />拜访时间：<input style="width: 50%" type="text" name="visitime" value=""><br> ' +
                    '合作意愿：<select style="width: 50%" name="intention"><option value="0" selected="selected">明确拒绝</option><option value="1">很弱</option><option value="2">一般</option><option value="3">强</option></select> <br>' +
                    '备注：<br><textarea name="note" value=""></textarea>' +
                    '<button style="float: left;margin: 10px;" type="button" class="saveVisit mui-btn mui-btn-danger">保存</button>'+
                    '</form>'+
                    '<form action="<?php echo U('delvisit');?>" method=post>'+
                    '<input type="hidden"  name="parkid" value="<?php echo ($park_info[id]); ?>" /> <input type="hidden"  name="id" value="" />'+
                    '<button style="float: right;margin: 10px;" type="button" class="removeVisit mui-btn mui-btn-danger">删除</button>' +
                    '<hr style="clear: both;margin: 5px"></li>'+'</form>';
                    var row = $(tmp);
                    row.find('.removeVisit').click(function(){
                        //$(this).parent().remove();
                        $(this).parent().submit();
                    });
                    row.find('.saveVisit').click(function(){
                        $(this).parent().submit();
                    });
                    $(this).prev().append(row);
                    return false;
                }
        );

        $('.removeVisit').click(function(){
                    //$(this).parent().remove();
                    $(this).parent().submit();
                }
        );
        $('.saveVisit').click(function(){
                    $(this).parent().submit();
                }
        );


        $('#addAdmin').click(function(){
                    var tmp = '<li class="myborder"> <form action="<?php echo U('saveadmin');?>" method=post><input type="hidden"  name="parkid" value="<?php echo ($park_info[id]); ?>" /> <input type="hidden"  name="id" value="" />停车场：<input type="text" name="parkname" readonly value="<?php echo ($park_info[shortname]); ?>"><br> ' +
                    '用户名：<input type="text"  name="username" value=""/>' +
                    '<h6>*用户名以001,002自增长</h6>'  +
                    '密码：<input type="password"  name="password" value=""/>' +
                    '<h6>*默认密码以前两位小写缩写字母+0102</h6>'   +
                    '姓名：<input type="text"  name="name" value=""/><br>' +
                    '职能：<br>' +
                    '<input style="width: 10%" type="checkbox" name="jobfunction[]" value="1" /><span>入口管理</span>'+
                    '<input style="width: 10%" type="checkbox" name="jobfunction[]" value="2" /><span>出口管理</span>'+
                    '<input style="width: 10%" type="checkbox" name="jobfunction[]" value="4" /><span>收费</span>'+'<br>'+
                    '<input style="width: 10%" type="checkbox" name="jobfunction[]" value="8" /><span>公司管理</span>'+
                    '<input style="width: 10%" type="checkbox" name="jobfunction[]" value="16" /><span>金额提现</span>'+'<br>'+
                    '<button style="float: left;margin: 10px;" type="button" class="saveAdmin mui-btn mui-btn-danger">保存</button>'+
                    '</form>'+
                    '<form action="<?php echo U('delAdmin');?>" method=post>'+
                    '<input type="hidden"  name="parkid" value="<?php echo ($park_info[id]); ?>" /> <input type="hidden"  name="id" value="" />'+
                    '<button style="float: right;margin: 10px;" type="button" class="removeAdmin mui-btn mui-btn-danger">删除</button>' +
                    '<hr style="clear: both;margin: 5px"></li>'+'</form>';
                    var row = $(tmp);
                    row.find('.removeAdmin').click(function(){
                        //$(this).parent().remove();
                        $(this).parent().submit();
                    });
                    row.find('.saveAdmin').click(function(){
                        $(this).parent().submit();
                    });
                    $(this).prev().append(row);
                    return false;
                }
        );

        $('.removeAdmin').click(function(){
                    //$(this).parent().remove();
                    $(this).parent().submit();
                }
        );
        $('.saveAdmin').click(function(){
                    $(this).parent().submit();
                }
        );
       
    })
</script>

</body>
</html>