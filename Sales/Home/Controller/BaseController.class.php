<?php
/**
 * 后台基础控制器
 * @Bin
 */
class BaseController extends \Think\Controller {

    /**
     * 后台控制器初始化
     */
    protected function _initialize(){
        // 获取当前用户ID
        define('UID',is_login());
        if( !UID ){// 还没登录 跳转到登录页面
            $this->redirect('Public/login');
        }
        /* 读取数据库中的配置 (之后可以把停车场类型放到数据库中)
        $config =   S('DB_CONFIG_DATA');
        if(!$config){     
            $config	=	D('Config')->lists();
            S('DB_CONFIG_DATA',$config);   
        }
        C($config); //添加配置
        */  
    }


    protected  function  getGiftInfo($code){
        $DriverGiftPack = M('DriverGiftpack');
        $map = array();
        $map['code'] = $code;
        $giftInfo = $DriverGiftPack->where($map)->getField('info');

        return $giftInfo;

    }
}
