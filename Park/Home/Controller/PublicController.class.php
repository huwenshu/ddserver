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
            $parkid = $data['parkid'];
            $permission = $data['jobfunction'];
            
            $ParkAdmin->where(array('id'=>$uid))->data(array('lastop'=>time()))->save();//更新上次操作时间戳
        }
        else{
           $this->ajaxMsg('用户名或者密码错误！');
        }

        $ParkInfo = M('ParkInfo');
        $con = array('id' => $parkid);
        $parkInfo = $ParkInfo->where($con)->find();

        if(!empty($parkInfo)){
            $parkFullName = $parkInfo['name'];
        }
        else{
            $this->ajaxMsg('用户名或者密码错误！');
        }


        $arr = array('parkid' => $parkid);
        $uuid = $this->createUUID($uid,$arr);

        $temp = array('uid' => $uid, 'uuid' =>$uuid, 'permission' => $permission, 'fullname' => $parkFullName);
        $this->ajaxOk($temp);
    }

    public function checkLogin($uid, $uuid){
        $data = $this->getUsercache($uid);
        if($data){
            if ($data['uuid'] == $uuid) {
                $this->ajaxOk('');
            }
            else{
                $this->ajaxFail();
            }
        }
        else{
            $this->ajaxFail();
        }

    }

    public function testscore(){
        $result = $this->addScore(1,10);
       if($result){
            echo "YES";
       }
       else{
            echo "False";
       }
    }


//    public function updatecarid(){
//        $ParkOrder = M('ParkOrder');
//        $data = $ParkOrder->select();
//
//        foreach($data as $key => $value){
//            $m = array();
//            $m['id'] = $value['id'];
//            $temp = array();
//
//
//            $DriverCar = M('DriverCar');
//            $map = array();
//            $map['driverid'] = $value['uid'];;
//            $map['status'] = 1;
//            $car = $DriverCar->where($map)->find();
//            if(empty($car)){
//                $temp['carid'] = "";
//            }
//            else{
//                $temp['carid'] = $car['carid'];
//            }
//
//            $ParkOrder->where($m)->save($temp);
//            dump($temp);
//
//        }
//
//    }

}