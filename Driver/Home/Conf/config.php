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


    //CVS的Log记录地址
    'CSV_LOG_PATH' => './Public/Logs',


    'WX_API_URL'    =>  "https://api.weixin.qq.com/cgi-bin/",

    //自己公司微信参数
    'USERNAME_WEIXIN' => "gh_6f67ef5e0539",
    'APPID' =>  'wxd417c2e70f817f89',
    'APPSECRET' =>  '14f025315fecb3bd1bdfc1624338605c' ,
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
                'name' => '附近',
                'url' => 'https://open.weixin.qq.com/connect/oauth2/authorize?appid=wxd417c2e70f817f89&redirect_uri=http%3a%2f%2fdriver.duduche.me%2fdriver.php%2fhome%2fweixin%2fmenuCallBack%2f&response_type=code&scope=snsapi_base&state=map#wechat_redirect'
            ),
            array(
                'type' => 'view',
                'name' => '省钱',
                'url' => 'https://open.weixin.qq.com/connect/oauth2/authorize?appid=wxd417c2e70f817f89&redirect_uri=http%3a%2f%2fdriver.duduche.me%2fdriver.php%2fhome%2fweixin%2fmenuCallBack%2f&response_type=code&scope=snsapi_base&state=discover#wechat_redirect'
            ),
            array(
                'type' => 'view',
                'name' => '我的',
                'url' => 'https://open.weixin.qq.com/connect/oauth2/authorize?appid=wxd417c2e70f817f89&redirect_uri=http%3a%2f%2fdriver.duduche.me%2fdriver.php%2fhome%2fweixin%2fmenuCallBack%2f&response_type=code&scope=snsapi_base&state=userinfo#wechat_redirect'
            ),

        )
    ),

    //特殊的测试openid;
    'OPENID' => array('oMjtxuH5YZ_6TSkGGLUWvW64aiHQ','oMjtxuLs_dp8C_rTF1RHKt2QjUKg','oMjtxuEBNoFf9FdJ0X7XD1oHmr4s', 'oMjtxuBMky9zVTaoRRlqqJZBb0co','oMjtxuAXEFgHuGOZM5bQhqfuEhqA','oMjtxuGib3cwMCHBCwQpd7U9OtDU'),

    // 配置邮件发送服务器
    'MAIL_HOST' =>'smtp.exmail.qq.com',
    'MAIL_SMTPAUTH' =>TRUE, //启用smtp认证
    'MAIL_USERNAME' =>'dubin@duduche.me',
    'MAIL_FROM' =>'dubin@duduche.me',
    'MAIL_FROMNAME' =>'Bin',
    'MAIL_PASSWORD' =>'Njudb07',
    'MAIL_CHARSET' =>'utf-8',
    'MAIL_ISHTML' =>TRUE, // 是否HTML格式邮件


    //图片七牛访问路径
    'PARK_IMG_QINIU' =>'http://7xispd.com1.z0.glb.clouddn.com',

    //默认的销售负责人ID
    'DEFAULT_SALES' => 6,

    //微信推送消息客服请求URL
    'WX_CUSTOM_URL' => 'https://api.weixin.qq.com/cgi-bin/message/custom/send?access_token=',

    //发送给用户的客服信息-文本模板
    'CUSTOM_TEXT_TPL' =>'{
        "touser":"%s",
        "msgtype":"text",
         "text":
        {
             "content":"%s"
        }
    }',

    //微信推送模板消息请求URL
    'WX_TEMPLATE_URL' => 'https://api.weixin.qq.com/cgi-bin/message/template/send?access_token=',

    //模板的ID
    'TEMPLATE_ID_PRE' => 'X6Wb3pKX-0sA_kGdD-kQ98jJvwB5pLsnqGDYOnERL6M',

    //模板跳转URL
    'TEMPLATE_REDIRECT_URL_PRE' => 'https://open.weixin.qq.com/connect/oauth2/authorize?appid=wxd417c2e70f817f89&redirect_uri=http%3a%2f%2fdriver.duduche.me%2fdriver.php%2fhome%2fweixin%2fmenuCallBack%2f&response_type=code&scope=snsapi_base&state=myjiesuan#wechat_redirect',

    //发送给用户的消息模板
    'NOTICE_TPL_PRE' => ' {
           "touser":"%s",
           "template_id":"%s",
           "url":"%s",
           "topcolor":"#000000",
           "data":{
                   "first": {
                       "value":"%s",
                       "color":"#000000"
                   },
                   "keyword1":{
                       "value":"%s",
                       "color":"#000000"
                   },
                   "keyword2": {
                       "value":"%s",
                       "color":"#000000"
                   },
                   "keyword3": {
                       "value":"%s",
                       "color":"#000000"
                   },
                   "remark":{
                       "value":"%s",
                       "color":"#000000"
                   }
           }
       }',

    //停车场类型
    'PARK_STYLE' => array (
        'DM' => '地面',
        'DXK' => '地下库',
        'LTCK' => '立体车库',
        'LUB' => '路侧边',
        'XQ' => '普通小区',
        'GDXQ' => '高档小区',
        'GWZX' => '购物中心',
        'DXCS' => '大型超市',
        'FWMD' => '消费门店',//原来是服务门店
        'SYXZL' => '商业写字楼',
        'JD' => '酒店',
        'YY' => '医院',
        'DWJG' => '单位机构',
        'SYTCC' => '商业停车场',
        'LYJD' => '景点',
        'ZDZJCR' => '自动闸机进入',
        'USERFX' => '人工放行',
        'ZYSF' => '中央收费',
        'BDWKF' => '不对外开放',
        'LDCD' => '流动车多',
        'SH' => '实惠'
    ),
);
