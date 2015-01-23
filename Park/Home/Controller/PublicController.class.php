<?php

/**
 * Park公共页面控制器
 * @Bin
 */
class PublicController extends BaseController {

    /**
     * 用户登录
     */
    public function login($parkname, $username, $password){
        $uid=null;

        $ParkAdmin = M('ParkAdmin');
        $map = array('parkname' =>$parkname, 'username' => $username, 'password' => strtoupper(md5($password)));
        $data = $ParkAdmin->where($map)->find();

        if(!empty($data)){
            $uid = $data['id'];
        }
        else{
           $this->ajaxMsg('用户名或者密码错误！');
        }

        $uuid = $this->createUUID($uid);
        $temp = array('uid' => $uid, 'uuid' =>$uuid, 'permission' => $data['jobfunction']);
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