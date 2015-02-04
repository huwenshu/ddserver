<?php

/**
 * 后台配置文件
 * 所有除开系统级别的后台配置
 */
return array(
    /* 数据缓存设置 */
    'DATA_CACHE_PREFIX'    => 'dududriver_', // 缓存前缀
    'DATA_CACHE_TYPE'      => 'File', // 数据缓存类型
    'DATA_CACHE_TIME'      =>  0,

    /* 数据库配置 */
    'DB_TYPE'   => 'mysqli', // 数据库类型
    'DB_HOST'   => '127.0.0.1', // 服务器地址
    'DB_NAME'   => 'dudu_parking', // 数据库名
    'DB_USER'   => 'root', // 用户名
    'DB_PWD'    => '',  // 密码
    'DB_PORT'   => '3306', // 端口
    'DB_PREFIX' => 'dudu_', // 数据库表前缀
    
    //应用类库不用命名空间
    'APP_USE_NAMESPACE'    =>    false,
    
    /* 调试配置 */
    'SHOW_PAGE_TRACE' => false,
    
    /* URL配置 */
    'URL_CASE_INSENSITIVE' => true, //默认false 表示URL区分大小写 true则表示不区分大小写
    'URL_MODEL'            => 1, //URL模式
    'URL_HTML_SUFFIX'       =>  NULL,
    'URL_PATHINFO_DEPR'    => '/', //PATHINFO URL分割符   
    
    /* 模板相关配置 */
    'TMPL_PARSE_STRING' => array(
        '__STATIC__' => __ROOT__ . '/Public/static',
        '__ADDONS__' => __ROOT__ . '/Public/' . MODULE_NAME . '/Addons',
        '__IMG__'    => __ROOT__ . '/Public/' . MODULE_NAME . '/images',
        '__CSS__'    => __ROOT__ . '/Public/' . MODULE_NAME . '/css',
        '__JS__'     => __ROOT__ . '/Public/' . MODULE_NAME . '/js',
    ),

    /* SESSION 和 COOKIE 配置 */
    'SESSION_PREFIX' => 'dudu_driver', //session前缀
    'COOKIE_PREFIX'  => 'dudu_driver_', // Cookie前缀 避免冲突

    /* 后台错误页面模板 */
    // 'TMPL_ACTION_ERROR'     =>  MODULE_PATH.'View/default/Public/error.html', // 默认错误跳转对应的模板文件
    // 'TMPL_ACTION_SUCCESS'   =>  MODULE_PATH.'View/default/Public/success.html', // 默认成功跳转对应的模板文件
    // 'TMPL_EXCEPTION_FILE'   =>  MODULE_PATH.'View/default/Public/exception.html',// 异常页面的模板文件
    
    //模版主题
    'DEFAULT_THEME'  	=> 	'default',
    'THEME_LIST'		=>	'default',



    'WX_API_URL'    =>  "https://api.weixin.qq.com/cgi-bin/",

    //自己公司微信参数
    'USERNAME_WEIXIN' => "gh_1b878e784651",
    'APPID' =>  'wx7402a94935807c76',
    'APPSECRET' =>  '59023166c76dbbb5d82e81318d514893' ,
    'WEIXIN_TOKEN'  => 'DUDUPARK2015',

    //微信支付参数，采用别的公司的配置
//    'APPID'=>  'wxd3c766afb2b3b774',
//    'APPSECRET' =>  '72fcf4b7f001dc9e37abaadbcc692c21',
//    'APPKEY' => '9ur6qRqjSPf6dRS2leYVW3Hul5X8DdW6HOwUwYNBngh9IOt5FgGXgn6floIw55ga1GE1VlsLyEIwORojscP0Q73gCE6bnG6hHughGVabE5cL94BaeecBYtI9d51ipW4i',
//    'SIGNTYPE'  =>  'sha1',
//    'PARTNERKEY' => '0a870e4e9d1b1376587bd414316aed86',//通加密串


    'HINT_TPL' => '<xml>
	<ToUserName><![CDATA[%s]]></ToUserName>
	<FromUserName><![CDATA[%s]]></FromUserName>
	<CreateTime>%s</CreateTime>
	<MsgType><![CDATA[%s]]></MsgType>
	<Content><![CDATA[%s]]></Content>
</xml>',

    'MENU' => array(
        'button'=>array(
        array(
            'type' => 'view',
            'name' => '现场',
            'url' => 'http://duduche.me/html/secenhtml/index.html'
        ),
        array(
            'name' => '用户',
            'sub_button' => array(
                array(
                    'type' => 'view',
                    'name' => '车位',
                    'url' => 'http://duduche.me/html/userhtml/index.html?m=map'
                ),
                array(
                    'type' => 'view',
                    'name' => '车位（找）',
                    'url' => 'http://duduche.me/html/userhtml/index.html?m=mapsearch'
                ),
                 array(
                    'type' => 'view',
                    'name' => '我-订单',
                    'url' => 'http://duduche.me/html/userhtml/index.html?m=myorder'
                ),
                array(
                    'type' => 'view',
                    'name' => '我-缴费',
                    'url' => 'http://duduche.me/html/userhtml/index.html?m=myjiesuan'
                ),
            )
        ),

        array(
            'type' => 'view',
            'name' => '销售',
            'url' => 'http://duduche.me/sales.php'
        ),
        )
    ),
);
