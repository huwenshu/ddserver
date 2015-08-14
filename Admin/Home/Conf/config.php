<?php

/**
 * 后台配置文件
 * 所有除开系统级别的后台配置
 */
return array(
    /* 数据缓存设置 */
    'DATA_CACHE_PREFIX'    => 'duduadmin_', // 缓存前缀
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
    'SESSION_PREFIX' => 'dudu_admin', //session前缀
    'SESSION_OPTIONS' => array('expire'=>30*24*3600,'path'=>RUNTIME_PATH.'Temp'),
    'VAR_SESSION_ID' => 'session_id',	//修复uploadify插件无法传递session_id的bug

    /* 后台错误页面模板 */
    'TMPL_ACTION_ERROR'     =>  MODULE_PATH.'View/default/Public/error.html', // 默认错误跳转对应的模板文件
    'TMPL_ACTION_SUCCESS'   =>  MODULE_PATH.'View/default/Public/success.html', // 默认成功跳转对应的模板文件
    'TMPL_EXCEPTION_FILE'   =>  MODULE_PATH.'View/default/Public/exception.html',// 异常页面的模板文件

    //模版主题
    'DEFAULT_THEME'  	=> 	'default',
    'THEME_LIST'		=>	'default',
    'TMPL_DETECT_THEME' => 	true, // 自动侦测模板主题

    //订单状态
    'ORDER_STATE' => array(-1 =>'生成订单', 0 => '预付',  1 => '进场', 2 => '结清', 3 => '离场'),

    //车位状态
    'PARK_STATE'  => array( 0 => '车位已满',  1 => '车位较少', 2 => '车位较多'),

    //礼品图片保存位置
    'GIFT_IMG' => 'uploads.duduche.me/images',

    //上传图片的FTP设置
    'UPLOAD_FTP'     =>    array(
        'host'     => '115.29.160.95', //服务器
        'port'     => 21, //端口
        'timeout'  => 90, //超时时间
        'username' => 'www', //用户名
        'password' => '2aed6eb9d'//密码
    ),

    //FTP上传地址
    'UPLOAD_FTP_PATH' => './default/Public/Uploads/images/',

    //CVS的Log记录地址
    'CSV_LOG_PATH' => './Public/Logs',

    //礼品送货方式
    'VISIT_TYPE' => array('Online' => 0, 'Offline' => 1),

    //自己公司微信参数
    'USERNAME_WEIXIN' => "gh_6f67ef5e0539",
    'APPID' =>  'wxd417c2e70f817f89',
    'APPSECRET' =>  '14f025315fecb3bd1bdfc1624338605c' ,
    'WEIXIN_TOKEN'  => 'DUDUPARK2015',

    'WX_API_URL'    =>  "https://api.weixin.qq.com/cgi-bin/",

    // 配置邮件发送服务器
    'MAIL_HOST' =>'smtp.exmail.qq.com',
    'MAIL_SMTPAUTH' =>TRUE, //启用smtp认证
    'MAIL_USERNAME' =>'dubin@duduche.me',
    'MAIL_FROM' =>'dubin@duduche.me',
    'MAIL_FROMNAME' =>'Bin',
    'MAIL_PASSWORD' =>'Njudb07',
    'MAIL_CHARSET' =>'utf-8',
    'MAIL_ISHTML' =>TRUE, // 是否HTML格式邮件

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

    //自动红包id数组
    'AUTO_GIFT' => array(
        233, 234, 235, 236, 237, 238, 239, 240, 241, 242
    ),

    //用于统计用的推广员id
    'AUTO_FROM_ID' => 12,

    //红包推送语言
    'AUTO_GIFT_MSG' => array(
        '两个黄鹂鸣翠柳，嘟嘟停车发券多；一行白鹭上青天，停车哪能不用券。赶紧来抢啊：',
        '身无彩凤双飞翼，心有灵犀一点通。客官，我知道你需要停车优惠券咯：',
        '这是你的益达！哦，不，这是你的停车券：',
        '爱对了是爱情，爱错了是青春；停对了是发票，停错了是罚单。领停车优惠券，快速找车位，告别罚单：',
        '主人，嘟嘟又给你送券来咯：',
        '世界那么大，停车那么难，给点券行不行？',
        '停车坐爱枫林晚，优惠拿券到手软：',
        '黑夜给了我黑色的眼睛，而我就喜欢用它来找停车优惠券：',
        '你停或者不停，券都在这里，不领白不领：',
        '据说土豪的三大特征是：喝酸奶不舔盖、去医院不看钱、去停车不用券。好吧，检验你是不是土豪的时刻来了，嘟嘟发停车优惠券了：'
    ),

    //免费停车场的状态
    'FREE_PARK_STATUS' => array(
        0 => '未审核',
        1 => '已审核',
        2 => '作废'
    ),

    //免费停车场TAG
    'FREE_PARK_TAG' => array(
        1 => '有时间限制',
        2 => '门店停车位',
        3 => '画有停车线',
        4 => '小道旁',
        5 => '断头路',
        6 => '空地',
        7 => '小区',
        8 => '老厂房'
    ),

    //付费停车场TAG
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
        'CGZLG' => '场馆/展览馆',
        'ZDZJCR' => '自动闸机进入',
        'USERFX' => '人工放行',
        'ZYSF' => '中央收费',
        'BDWKF' => '不对外开放',
        'LDCD' => '流动车多',
        'SH' => '实惠',
        'WYTG' => '网友提供',
    ),

    //FTP上传地址
    'PARK_UPLOAD_PATH' => './default/Public/Uploads/Park/',

    //图片相对访问路径
    'PARK_IMG_PATH' =>  './Public/Uploads/Park/',

    //图片七牛访问路径
    'PARK_IMG_QINIU' =>'http://7xispd.com1.z0.glb.clouddn.com',

    //个推参数
    'GETUI' => array(
        'GT_APPID' =>  '0J2ipM3D2V5WQDpWnQZC1',
        'GT_HOST' => 'http://sdk.open.api.igexin.com/apiex.htm',
        'GT_APPKEY' => 'qaHlPWSeAu9M5bG9D8akm7',
        'GT_MASTERSECRET' => 'FbBqTqUhAp7e4KCoiNmTx1',
    ),

);
