<?php
/**
 * Created by PhpStorm.
 * User: Bin
 * Date: 15/3/4
 * Time: 下午4:31
 */

class StatisticController extends BaseController {

    public function _initialize(){
        // 获取当前用户ID
        define('UID',$this->is_login());
        if( !UID ){// 还没登录 跳转到登录页面
            $this->redirect('Public/login');
        }
    }

    //重复购买的用户统计
    public function reorder(){
        $Model = new \Think\Model(); // 实例化一个model对象 没有对应任何数据表
        $sql = "SELECT uid FROM `dudu_park_order` WHERE state >-1 and pid <>1 and uid > 40 GROUP by uid HAVING COUNT(*) >1 ORDER BY uid desc";
        $uidList = $Model->query($sql);

        $ParkOrder = M('ParkOrder');
        $PayMent = M('PaymentRecord');
        $result = array();

        foreach($uidList as $key => $value){
            $temp = array();
            $uid = $value['uid'];
            $telephone = $this->getDriver($uid)['telephone'];
            $temp['uid'] = $uid;
            $temp['telephone'] = $telephone;
            $olist = array();

            $map = array();
            $map['uid'] = $uid;
            $map['state'] = array('EGT', 0);
            $orderList = $ParkOrder->where($map)->order('startime desc')->select();
            foreach($orderList as $k => $v){
                $o = array();
                $o['parkName'] = $this->getParkName($v['pid']);
                $o['carid'] = $v['carid'];
                $o['state'] = C('ORDER_STATE')[$v['state']];
                $o['id'] = $v[id];
                $o['startime'] = $v[startime];
                $o['entrytime'] = $v['entrytime'];
                $o['endtime'] = $v['endtime'];
                $o['leavetime'] = $v['leavetime'];
                $style = array(0=>'正常',1=>"用户处理", 2=>"管理员处理", 3=>"系统处理");
                $o['leaveStyle'] = $style[$v['driverleave']];
                if($v['state'] != 3){
                    $o['leaveStyle'] = "未离场";
                }

                $map = array();
                $map['oid'] = $v[id];
                $map['state'] = 1;
                $payList = $PayMent->where($map)->order('createtime desc')->select();
                $payRecord = "";
                foreach($payList as $vv){
                    $payRecord.= "M:".$vv['money']."-R:".$vv['money_r']."/";
                }
                $o['payRecord'] = $payRecord;

                array_push($olist, $o);

            }
            $temp['olist'] = $olist;

            array_push($result, $temp);

        }

        $this->List = $result;
        $this->meta_title = "二次下单用户统计页面";
        $this->display();
    }



}