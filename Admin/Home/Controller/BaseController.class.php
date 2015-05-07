<?php
/**
 * Created by PhpStorm.
 * User: Bin
 * Date: 15/3/4
 * Time: 下午4:29
 */

class BaseController extends \Think\Controller {


    /**
     * 检测验证码
     * @param  integer $id 验证码ID
     * @return boolean     检测结果
     */
    function check_verify($code, $id = 1){
        $verify = new \Think\Verify();
        return $verify->check($code, $id);
    }

    /**
     * 检测用户是否登录
     * @return integer 0-未登录，大于0-当前登录用户ID
     */
    function is_login(){
        $user = session('admin_auth');
        if (empty($user)) {
            return 0;
        } else {
            return  $user['uid'];
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
*  @desc $gid 获取礼品类型
*  @param $gid
*/
    protected function getGiftType($gid)
    {
        $ParkInfo = M('GiftList');
        $map = array();
        $map['id'] = $gid;
        $giftData = $ParkInfo->where($map)->find();
        if(!empty($giftData)) {
            return  $giftData['type'];
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
     *  @desc 根据uid 获取车主
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


    //获取红包的简介
    protected  function  getGiftInfo($code){
        $DriverGiftPack = M('DriverGiftpack');
        $map = array();
        $map['code'] = $code;
        $giftInfo = $DriverGiftPack->where($map)->getField('info');

        return $giftInfo;

    }

    //生产红包code
    protected function guid(){
        if (function_exists('com_create_guid')){
            return trim(com_create_guid(), '{}');
        }else{
            mt_srand((double)microtime()*10000);//optional for php 4.2.0 and up.
            $charid = strtoupper(md5(uniqid(rand(), true)));
            $hyphen = chr(45);// "-"
            $uuid = substr($charid, 0, 8).$hyphen
                .substr($charid, 8, 4).$hyphen
                .substr($charid,12, 4).$hyphen
                .substr($charid,16, 4).$hyphen
                .substr($charid,20,12);
            return $uuid;
        }
    }
}
