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
}
