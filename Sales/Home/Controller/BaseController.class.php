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

    /**
     * @desc 封装curl的调用接口，post的请求方式
     */
    protected function doCurlPostRequest($url, $requestString, $timeout = 5) {
        if($url == "" || $requestString == "" || $timeout <= 0){
            return false;
        }

        $con = curl_init((string)$url);
        curl_setopt($con, CURLOPT_HEADER, false);
        curl_setopt($con, CURLOPT_POSTFIELDS, $requestString);
        curl_setopt($con, CURLOPT_POST, true);
        curl_setopt($con, CURLOPT_RETURNTRANSFER,true);
        curl_setopt($con, CURLOPT_TIMEOUT, (int)$timeout);

        return curl_exec($con);
    }

    /**
     * @desc 封装curl的调用接口，get的请求方式
     */
    protected function doCurlGetRequest($url, $data = array(), $timeout = 10) {
        if($url == "" || $timeout <= 0){
            return false;
        }
        if($data != array()) {
            $url = $url . '?' . http_build_query($data);
        }
        $con = curl_init((string)$url);
        curl_setopt($con, CURLOPT_HEADER, false);
        curl_setopt($con, CURLOPT_RETURNTRANSFER,true);
        curl_setopt($con, CURLOPT_TIMEOUT, (int)$timeout);
        return curl_exec($con);
    }

    /**
     * @desc tags array => string
     */
    protected function arrayToString($arr){
        if(empty($arr)){
            return '';
        }
        else{
            $result = '|';
            foreach($arr as $value){
                $result .= $value.'|';
            }
            return $result;
        }
    }
}
