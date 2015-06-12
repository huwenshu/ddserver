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

    //停车场tag
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
        'ZDZJCR' => '自动闸机进入',
        'USERFX' => '人工放行',
        'ZYSF' => '中央收费',
        'BDWKF' => '不对外开放',
        'LDCD' => '流动车多',
        'SH' => '实惠',
    ),

    //停车场tag类型
    'PARK_STYLE_CAT' => array (
        'DM' => '停车场空间',
        'XQ' => '停车场类型',
        'ZDZJCR' => '收费方式',
        'LDCD' => '停放车辆',
    ),

    //停车场合作状态
    'PARK_COR_STATE' => array (
        'PRETOUCH' => -1,   //未接触
        'TOUCH' => -2,      //在接触
        'FIND' => 0,        //找到决策人
        'TEST' => 2,        //测试中
        'CORP' => 1,        //已合作
        'INFO' => 3,        //已信息化
    ),

    //停车场合作状态
    'PARK_COR' => array (
        '4' => '已合作',
        '3' => '测试中',
        '2' => '找到决策人',
        '1' => '在接触',
        '0' => '未接触',
    ),

    //停车场信息化状态
    'PARK_INF' => array (
        '0' => '未信息化',
        '1' => '信息化',
    ),


    //停车场活动类型
    'PARK_AC_TYPE' => array (
        0 => null,
        1 => '5元推广补助-离场',
        2 => '5元推广补助-进场',
    ),

    //停车场驻场活动类型
    'PARK_E_TYPE' => array (
        1 => '新用户优惠',
        2 => '固定价格停车',
    ),

    //上传图片的FTP设置
    'UPLOAD_FTP'     =>    array(
        'host'     => '115.29.160.95', //服务器
        'port'     => 21, //端口
        'timeout'  => 90, //超时时间
        'username' => 'www', //用户名
        'password' => '2aed6eb9d'//密码
    ),

    //FTP上传地址
    'PARK_UPLOAD_PATH' => './default/Public/Uploads/Park/',

    //图片相对访问路径
    'PARK_IMG_PATH' =>  './Public/Uploads/Park/',

    //图片七牛访问路径
    'PARK_IMG_QINIU' =>'http://7xispd.com1.z0.glb.clouddn.com',

    //附近停车场距离，单位米
    'NEAR_DIS' => 500,

);
