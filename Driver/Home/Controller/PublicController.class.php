<?php

/**
 * Driver公关页面控制器
 * @Bin
 */
class PublicController extends BaseController {

    /**
     * 用户登录
     */
    public function login($phone = null, $carid = null){
        $uid=null;

        $Driver = M('DriverInfo');
        $map = array('telephone' => $phone);
        $data = $Driver->where($map)->find();

        if(!empty($data)){
            $uid = $data['id'];
            //todo:更新车牌号
        }
        else{
            $arr['telephone'] = $phone;
            $arr['carid'] = $carid;
            $arr['createtime'] = date('Y-m-d H:i:s');
            $uid = $Driver->add($arr);
        }
        $uuid = $this->createUUID($uid);
        $temp = array('uid' => $uid, 'uuid' =>$uuid);
        $this->ajaxOk($temp);
    }

    public function checkLogin($uid, $uuid){
        $data = $this->getUsercache($uid);
        if($data){
            if ($data['uuid'] == $uuid) {
                $this->ajaxOk();
            }
            else{
                $this->ajaxFail();
            }
        }
        else{
            $this->ajaxFail();
        }

    }
}