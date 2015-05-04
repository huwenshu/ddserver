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

    /**
     *  @desc $pid 获取停车场名字
     *  @param $pid
     */
    protected function getParkName($pid)
    {
        $ParkInfo = M('ParkInfo');
        $map = array();
        $map['id'] = $pid;
        $parkData = $ParkInfo->where($map)->find();
        if(!empty($parkData)) {
            return  $parkData['name'];
        }
        else{
            return null;
        }

    }

    /**
     *  @desc $gid 获取礼品名字
     *  @param $gid
     */
    protected function getGiftName($gid)
    {
        $ParkInfo = M('GiftList');
        $map = array();
        $map['id'] = $gid;
        $giftData = $ParkInfo->where($map)->find();
        if(!empty($giftData)) {
            return  $giftData['name'];
        }
        else{
            return null;
        }

    }

    /**
     *  @desc $gid 获取礼品
     *  @param $gid
     */
    protected function getGift($gid)
    {
        $ParkInfo = M('GiftList');
        $map = array();
        $map['id'] = $gid;
        $giftData = $ParkInfo->where($map)->find();
        if(!empty($giftData)) {
            return  $giftData;
        }
        else{
            return null;
        }

    }



    /**
     *  @desc 根据UID获得默认车牌号
     *  @param int $uid
     */
    protected function getDefualtCarid($uid){
        $DriverCar = M('DriverCar');
        $map = array();
        $map['driverid'] = $uid;
        $map['status'] = 1;
        $car = $DriverCar->where($map)->find();
        if(empty($car)){
            return null;
        }
        else{
            return $car['carid'];
        }
    }


    /**
     *  @desc 积分方法
     *  @param int $aid 管理员id
     *  @param int $add 增加的积分
     */
    protected function addScore($aid, $add){

        $ParkAdmin = M('ParkAdmin');
        $map = array();
        $map['id'] = $aid;
        $addData = $ParkAdmin->where($map)->save(array('score'=>array('exp','score+'.$add),'lastop'=>time()));

        if(empty($addData)){
            return false;
        }
        else{
            return true;
        }
    }

    /**
     *  @desc 检查奖励积分，未超过，则缓存
     *  @param int $uid 管理员id
     *  @param int $add 增加的积分
     */
    protected function cacheScore($uid,$add){

        $timeStr = date('Ymd');
        $key = 'score_'.$uid.'_'.$timeStr;
        $score = S($key);
        dump($score);
        $limit = C('SCORE_LIMIT');
        if(!$score){//不存在cache
            if($add > $limit){
                return false;
            }
            else{
                $score = $add;
                S($key, $score, 24*60*60);
            }
        }
        else{
            $score = $score + $add;
            if($score > $limit){
                return false;
            }
            else{
                S($key, $score, 24*60*60);
            }
        }

        return true;

    }

    /**
     *  @desc  发送邮件
     *  @param $adid
     */
    protected function sendEmail($mail, $title, $content)
    {
        return SendMail($mail, $title, $content);
    }

    /**
     *  @desc  二位数组去重
     *  @param $arr 去重的数组
     *  @param $key   去除的子列
     */
    protected function  assoc_unique($arr, $key){
        $tmp_arr = array();
        foreach($arr as $k => $v)
        {
            if(in_array($v[$key], $tmp_arr))//搜索$v[$key]是否在$tmp_arr数组中存在，若存在返回true
            {
                unset($arr[$k]);
            }
            else {
                $tmp_arr[] = $v[$key];
            }
        }

        return $arr;
    }

}
