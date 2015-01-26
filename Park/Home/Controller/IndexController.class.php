<?php

use Think\Controller;

class IndexController extends BaseController {

	private $uid;
	private $lat;
	private $lng;

	public function _initialize(){
		$uid = I('get.uid');
		$uuid = I('get.uuid');
		$this->uid = $uid;
		$data = $this->getUsercache($uid);
		if($data){
			if ($data['uuid'] == $uuid) {
				$this->uid = $uid;
				return;
			}
			else{
				$this->ajaxFail();
			}
		}
		else{
			$this->ajaxFail();
		}
	}

    public function index(){
        $this->show('<style type="text/css">*{ padding: 0; margin: 0; } div{ padding: 4px 48px;} body{ background: #fff; font-family: "微软雅黑"; color: #333;font-size:24px} h1{ font-size: 100px; font-weight: normal; margin-bottom: 12px; } p{ line-height: 1.8em; font-size: 36px } a,a:hover,{color:blue;}</style><div style="padding: 24px 48px;"> <h1>:)</h1><p>欢迎使用 <b>ThinkPHP</b>！</p><br/>版本 V{$Think.version}</div><script type="text/javascript" src="http://ad.topthink.com/Public/static/client.js"></script><thinkad id="ad_55e75dfae343f5a1"></thinkad><script type="text/javascript" src="http://tajs.qq.com/stats?sId=9347272" charset="UTF-8"></script>','utf-8');
    }

	//返回附近停车场接口

	public function test($phone){
     		$result = array(
						'code'=>100,
						'data'=>'Hello,'.$phone.'!'
				  );

    		$this->ajaxReturn($result,'jsonp');
    }

	/*
     *  @desc 获取预付，但未进场的列单
    */
	public function getEntries(){
		$cache = $this->getUsercache($this->uid);
		$data = $cache['data'];
		$parkid = $data['parkid'];

		$Order = M('ParkOrder');
		$con = array('pid' => $parkid, 'state' => 0);
		$orderData = $Order->where($con)->select();

		$result = array();
		foreach($orderData as $key => $value){
			$tmp = array();
			$tmp['oid'] = $value['id'];
			$driverId = $value['uid'];
			$Driver = M('DriverInfo');
			$con1 = array('id' => $driverId);
			$driverData = $Driver->where($con1)->find();
			$tmp['carid'] = $driverData['carid'];
			$tmp['orderTime'] = $value['startime'];

			array_push($result, $tmp);
		}

		$this->ajaxOk($result);
	}

	/*
     *  @desc 车辆进场，设置状态为在场
	 *  @param oid	订单id
    */
	public function setEntry($oid){
		$cache = $this->getUsercache($this->uid);
		$data = $cache['data'];
		$parkid = $data['parkid'];

		$Order = M('ParkOrder');
		$con = array('id' => $oid, 'pid' => $parkid);
		$updateData['state'] = 1;
		$orderData = $Order->where($con)->save($updateData);

		if($orderData){
			$this->ajaxOk("");
		}
		else{
			$this->ajaxMsg("进场失败！");
		}


	}

	/*
     *  @desc 获取在场车辆列表
    */
	public function getStops(){
		$cache = $this->getUsercache($this->uid);
		$data = $cache['data'];
		$parkid = $data['parkid'];

		$Order = M('ParkOrder');
		$con = array('pid' => $parkid, 'state' => 1);
		$orderData = $Order->where($con)->select();

		$result = array();
		foreach($orderData as $key => $value){
			$tmp = array();
			$tmp['oid'] = $value['id'];
			$driverId = $value['uid'];
			$Driver = M('DriverInfo');
			$con1 = array('id' => $driverId);
			$driverData = $Driver->where($con1)->find();
			$tmp['carid'] = $driverData['carid'];
			$tmp['startTime'] = $value['startime'];

			array_push($result, $tmp);
		}

		$this->ajaxOk($result);


	}

	/*
     *  @desc 获取准备离场车辆列表
    */
	public function getLeavings(){
		$cache = $this->getUsercache($this->uid);
		$data = $cache['data'];
		$parkid = $data['parkid'];

		$Order = M('ParkOrder');
		$con = array('pid' => $parkid, 'state' => 2);
		$orderData = $Order->where($con)->select();

		$result = array();
		foreach($orderData as $key => $value){
			$tmp = array();
			$tmp['oid'] = $value['id'];
			$driverId = $value['uid'];
			$Driver = M('DriverInfo');
			$con1 = array('id' => $driverId);
			$driverData = $Driver->where($con1)->find();
			$tmp['carid'] = $driverData['carid'];
			$tmp['startTime'] = $value['startime'];
			$tmp['endTime'] = $value['endtime'];

			array_push($result, $tmp);
		}

		$this->ajaxOk($result);


	}

	/*
     *  @desc 车辆离场
	 *  @param oid	订单id
    */
	public function setLeave($oid){
		$cache = $this->getUsercache($this->uid);
		$data = $cache['data'];
		$parkid = $data['parkid'];

		$Order = M('ParkOrder');
		$con = array('id' => $oid, 'pid' => $parkid);
		$updateData['state'] = 3;
		$orderData = $Order->where($con)->save($updateData);

		if($orderData){
			$this->ajaxOk("");
		}
		else{
			$this->ajaxMsg("进场失败！");
		}


	}
}