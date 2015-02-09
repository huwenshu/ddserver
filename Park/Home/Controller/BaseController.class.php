<?php
/**
 * 后台基础控制器
 * @Bin
 */
class BaseController extends \Think\Controller {

    protected function createUUID($uid, $data){
        mt_srand((double)microtime()*10000);//optional for php 4.2.0 and up.
        $charid = strtoupper(md5(uniqid(rand(), true)));
        $hyphen = chr(45);// "-"
        $uuid =  substr($charid, 0, 8).$hyphen
                .substr($charid, 8, 4).$hyphen
                .substr($charid,12, 4).$hyphen
                .substr($charid,16, 4).$hyphen
                .substr($charid,20,12);

        $cachekey = $this->getCacheKey($uid);



        S($cachekey,array(
            'uid' => $uid,
            'uuid' =>$uuid,
            'data' =>$data,
        ),C('DATA_CACHE_TIME'));

        return $uuid;
    }

    protected function getCacheKey($uid){
        return '____parkadmincachekey___'.$uid;
    }

    protected function getUsercache($uid){
        $key = $this->getCacheKey($uid);
        $data = S($key);
        return $data;
    }

    protected function sendmsg($code, $data){
        $result = array(
                    'code'=>$code,
                    'data'=>$data
                );

        $this->ajaxReturn($result,'jsonp');
    }

    
    protected function ajaxOk($data){
        $this->sendmsg(0,$data);
        exit;
    }
    protected function ajaxMsg($msg){
        $this->sendmsg(10,$msg);
        exit;
    }
    protected function ajaxFail(){
        $this->sendmsg(100,"");
        exit;
    }

    /**
     *  @desc 根据uid 去车主的车牌
     *  @param $uid
     */
    protected function getDriver($uid)
    {
        $Driver = M('DriverInfo');
        $map = array();
        $map['id'] = $uid;
        $driverData = $Driver->where($map)->find();
        if(!empty($driverData)){
            return $driverData;
        }
        else{
            return null;
        }

    }

    /**
     *  @desc 根据adid 获取管理员名字
     *  @param $adid
     */
    protected function getAdmin($adid)
    {
        $ParkAdmin = M('ParkAdmin');
        $map = array();
        $map['id'] = $adid;
        $adminData = $ParkAdmin->where($map)->find();
        if(!empty($adminData)) {
            return  $adminData['name'];
        }
        else{
            return null;
        }

    }


}
