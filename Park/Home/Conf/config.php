<?php
return array(
	//'配置项'=>'配置值'
	'DATA_CACHE_PREFIX'    => 'dudupark_', // 缓存前缀
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
    'SHOW_PAGE_TRACE' => true,
    
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
    'SESSION_PREFIX' => 'dudu_park', //session前缀
    'COOKIE_PREFIX'  => 'dudu_park_', // Cookie前缀 避免冲突

    /* 后台错误页面模板 */
//    'TMPL_ACTION_ERROR'     =>  MODULE_PATH.'View/default/Public/error.html', // 默认错误跳转对应的模板文件
//    'TMPL_ACTION_SUCCESS'   =>  MODULE_PATH.'View/default/Public/success.html', // 默认成功跳转对应的模板文件
//    'TMPL_EXCEPTION_FILE'   =>  MODULE_PATH.'View/default/Public/exception.html',// 异常页面的模板文件
    
    //模版主题
    'DEFAULT_THEME'  	=> 	'default',
    'THEME_LIST'		=>	'default',
    //'TMPL_DETECT_THEME' => 	true, // 自动侦测模板主题


    // 配置邮件发送服务器
    'MAIL_HOST' =>'smtp.exmail.qq.com',
    'MAIL_SMTPAUTH' =>TRUE, //启用smtp认证
    'MAIL_USERNAME' =>'dubin@duduche.me',
    'MAIL_FROM' =>'dubin@duduche.me',
    'MAIL_FROMNAME' =>'Bin',
    'MAIL_PASSWORD' =>'Njudb07',
    'MAIL_CHARSET' =>'utf-8',
    'MAIL_ISHTML' =>TRUE, // 是否HTML格式邮件

    'SCORE' => array('state' => 0, 'in' => 0, 'out' =>0),

    //CVS的Log记录地址
    'CSV_LOG_PATH' => './Public/Logs',

    //礼品送货方式
    'VISIT_TYPE' => array('Online' => 0, 'Offline' => 1),

);