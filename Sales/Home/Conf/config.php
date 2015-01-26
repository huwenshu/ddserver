<?php

/**
 * 后台配置文件
 * 所有除开系统级别的后台配置
 */
return array(
    /* 数据缓存设置 */
    'DATA_CACHE_PREFIX'    => 'dudusales_', // 缓存前缀
    'DATA_CACHE_TYPE'      => 'File', // 数据缓存类型
    

    /* 用户相关设置 */
    'USER_MAX_CACHE'     => 1000, //最大缓存用户数

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
    'SESSION_PREFIX' => 'dudu_sales', //session前缀
    'SESSION_EXPIRE' => '300000',        //session过期时间
    'COOKIE_PREFIX'  => 'dudu_sales_', // Cookie前缀 避免冲突
    'VAR_SESSION_ID' => 'session_id',	//修复uploadify插件无法传递session_id的bug

    /* 后台错误页面模板 */
//    'TMPL_ACTION_ERROR'     =>  MODULE_PATH.'View/default/Public/error.html', // 默认错误跳转对应的模板文件
//    'TMPL_ACTION_SUCCESS'   =>  MODULE_PATH.'View/default/Public/success.html', // 默认成功跳转对应的模板文件
//    'TMPL_EXCEPTION_FILE'   =>  MODULE_PATH.'View/default/Public/exception.html',// 异常页面的模板文件
    
    //模版主题
    'DEFAULT_THEME'  	=> 	'default',
    'THEME_LIST'		=>	'default',
    //'TMPL_DETECT_THEME' => 	true, // 自动侦测模板主题

    //停车场类型
    'PARK_STYLE' => array (
        'DXK' => '地下库',
        'DM' => '地面',
        'LTCK' => '立体车库',
        'LUB' => '路侧边',
        'FWMD' => '服务门店',
        'XQ' => '小区',
        'SYXZL' => '商业写字楼',
        'GWZX' => '购物中心',
        'JD' => '酒店',
        'DXCS' => '大型超市',
        'ZYSF' => '中央收费',
        'ZDZJCR' => '自动闸机进入',
        'USERFX' => '人工放行',
    ),

);