<?php

use Think\Controller;

class IndexController extends BaseController {

	private $uid;
	private $lat;
	private $lng;

//	public function _initialize(){
//		$uid = I('get.uid');
//		$uuid = I('get.uuid');
//		$this->uid = $uid;
//		$data = $this->getUsercache($uid);
//		if($data){
//			if ($data['uuid'] == $uuid) {
//				$this->uid = $uid;
//				return;
//			}
//			else{
//				$this->ajaxFail();
//			}
//		}
//		else{
//			$this->ajaxFail();
//		}
//	}

    public function index(){
        $this->show('<style type="text/css">*{ padding: 0; margin: 0; } div{ padding: 4px 48px;} body{ background: #fff; font-family: "微软雅黑"; color: #333;font-size:24px} h1{ font-size: 100px; font-weight: normal; margin-bottom: 12px; } p{ line-height: 1.8em; font-size: 36px } a,a:hover,{color:blue;}</style><div style="padding: 24px 48px;"> <h1>:)</h1><p>欢迎使用 <b>ThinkPHP</b>！</p><br/>版本 V{$Think.version}</div><script type="text/javascript" src="http://ad.topthink.com/Public/static/client.js"></script><thinkad id="ad_55e75dfae343f5a1"></thinkad><script type="text/javascript" src="http://tajs.qq.com/stats?sId=9347272" charset="UTF-8"></script>','utf-8');
    }

	//返回附近停车场接口
	public function search($lat,$lng){
		$this->lat = $lat;
		$this->lng = $lng;
		$Park = M('ParkInfo');
		$gap = 0.1;
		$condition = ($lat - $gap).'<lat and lat<'.($lat + $gap).' and '.($lng - $gap).'<lng and lng<'.($lng + $gap);//.' and status=1';
		$list = $Park->where($condition)->limit(10)->select();

		usort($list, array($this, "distance_sort"));	//按距离远近排序

		//封装返回值
		$result = array();
		foreach($list as $key => $value){
			$tmp = array();
			$tmp['pid'] = $value['id'];
			$tmp['name'] = $value['name'];
			$tmp['rules'] = $value['chargingrules'];
			$tmp['address'] = $value['address'];
			$tmp['prepay'] = $value['prepay'];
			$tmp['lat'] = $value['lat'];
			$tmp['lng'] = $value['lng'];
			$tmp['spacesum'] = $value['spacesum'];
			$tmp['parkstate'] = $value['parkstate'];
			$tmp['note'] = $value['note'];
//			$tmp['dis'] = $this->getDistance($value['lat'],$value['lng'],$this->lat,$this->lng);
//			$tmp['llat'] = $this->lat;
//			$tmp['llng'] = $this->lng;
			array_push($result, $tmp);
		}
		$this->ajaxOk($result);

	}

   //生成预付订单借口
	public function genOrder($pid){

		$Park = M('ParkInfo');
		$map = array('id' => $pid);
		$parkinfo = $Park->where($map)->find();
		if(empty($parkinfo)){
			$this->ajaxMsg("停车场信息错误");
		}

		$Order = M('ParkOrder');
		$arr['uid'] = I('get.uid');
		$arr['pid'] = $pid;
		$arr['state'] = -1;
		$arr['startime'] = date("Y-m-d H:i:s",0);
		$arr['endtime'] = date("Y-m-d H:i:s",0);
		$arr['creater'] = I('get.uid');
		$arr['createtime'] = date("Y-m-d H:i:s");
		$arr['updater'] = I('get.uid');
		$oid = $Order->add($arr);

		if(empty($oid)){
			$this->ajaxMsg("创建订单失败");
		}

		$Payment = M('PaymentRecord');
		$temp['oid'] = $oid;
		$temp['money'] = $parkinfo['prepay'];
		$temp['state'] = 0;
		$temp['creater'] = I('get.uid');
		$temp['createtime'] = date("Y-m-d H:i:s");
		$temp['updater'] = I('get.uid');
		$prid = $Payment->add($temp);

		if(empty($prid)){
			$this->ajaxMsg("创建支付消息失败");
		}


		$commonUtil = new \Home\Common\Weixin\Pay\CommonUtil();
		$wxPayHelper = new \Home\Common\Weixin\Pay\WxPayHelper();

		$wxPayHelper->setParameter("bank_type", "WX");
		$wxPayHelper->setParameter("body", "预付停车费");
		$wxPayHelper->setParameter("partner", "1220503701");
		$wxPayHelper->setParameter("out_trade_no", $prid);
		$wxPayHelper->setParameter("total_fee", "1");
		$wxPayHelper->setParameter("fee_type", "1");
		$wxPayHelper->setParameter("notify_url", "http://duduparking.com/test/test_receiver.php");
		$wxPayHelper->setParameter("spbill_create_ip", get_client_ip());
		$wxPayHelper->setParameter("input_charset", "UTF-8");

		$result = $wxPayHelper->create_biz_package();
		//$this->ajaxReturn($result,'jsonp');
		$this->ajaxOk($result);

	}

	//处理预付回调函数
	public function genOrderDone(){



		if(!trade_state){
			$prid = I('post.out_trade_no');

			$Payment = M('PaymentRecord');
			$data['state'] = 1;
			$con1 = 'id='.$prid;
			$Payment->where($con1)->save($data); // 根据条件更新记录
			$pay = $Payment->where($con1)->find();

			$Order = M('ParkOrder');
			$arr['state'] = 0;
			$con2 = 'id='.$pay['oid'];
			$Order->where($con2)->save($arr); // 根据条件更新记录

			return "Success";

		}

	}

	/*
	 * @desc 查询最后的若干数量的订单，或者查询最新的一条未支付订单
	 * @last int 0-所有订单/1-最后订单
	*/

	public  function getOrder($last){
		$this->uid =1;
		$Order = M('ParkOrder');
		if($last){
			$con['uid'] = $this->uid;
			$con['state'] = array(0,1,'OR');
		}
		else{
			$con['uid'] = $this->uid;
		}

		$orderData = $Order->where($con)->select();

		$result = array();
		foreach($orderData as $key => $value){
			$tmp['oid'] = $value['id'];
			$tmp['startTime'] = $value['startime'];

			$Park = M('ParkInfo');
			$parkInfo = $Park->where('id = '.$value['pid'])->find();
			$tmp['parkname'] = $parkInfo['name'];
			$tmp['address'] = $parkInfo['address'];

			array_push($result, $tmp);
		}

		$this->ajaxOk($result);
	}

	//获得IP地址
	protected function get_client_ip() {
		if ($_SERVER['REMOTE_ADDR']) { $cip = $_SERVER['REMOTE_ADDR']; }
		elseif (getenv("REMOTE_ADDR")) { $cip = getenv("REMOTE_ADDR");  }
		elseif (getenv("HTTP_CLIENT_IP")) { $cip = getenv("HTTP_CLIENT_IP"); }
		else {  $cip = "127.0.0.1"; }

		return $cip;
	}

	public function test($phone){
     		$result = array(
						'code'=>100,
						'data'=>'Hello,'.$phone.'!'
				  );

    		$this->ajaxReturn($result,'jsonp');
    }


	//距离比较函数
	protected function distance_sort($v1,$v2){
		$dis1 = $this->getDistance($v1['lat'],$v1['lng'],$this->lat,$this->lng);
		$dis2 = $this->getDistance($v2['lat'],$v2['lng'],$this->lat,$this->lng);

        if($dis1 < $dis2) {
			return -1;
		} elseif ($dis1 > $dis2)  {
			return 1;
		} else {
			return 0;
		}
    }
}