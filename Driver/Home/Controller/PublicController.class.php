<?php

require_once(dirname(__FILE__) . '/../Common/getui/' . 'IGt.Push.php');
require_once(dirname(__FILE__) . '/../Common/Weixin/Pay/' . 'WxPay.config.php');

//推送
define('GT_APPKEY','SmksDDicdNA2GtpF4l7Sc5');
define('GT_APPID','dpEB6vgxrFABEctm95ZsB3');
define('GT_MASTERSECRET','wTd4AqonHlArztm0xiaYJ4');
define('GT_HOST','http://sdk.open.api.igexin.com/apiex.htm');

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


	protected function IGtNotificationTemplateDemo($title, $notice, $msg){
        $template =  new IGtNotificationTemplate();
        $template->set_appId(GT_APPID);//应用appid
        $template->set_appkey(GT_APPKEY);//应用appkey
        $template->set_transmissionType(1);//透传消息类型
        $template->set_transmissionContent($msg);//透传内容
        $template->set_title($title);//通知栏标题
        $template->set_text($notice);//通知栏内容
        $template->set_logo("http://duduche.me/html/userhtml/img/icon.png");//通知栏logo
        $template->set_isRing(true);//是否响铃
        $template->set_isVibrate(true);//是否震动
        $template->set_isClearable(true);//通知栏是否可清除
        // iOS推送需要设置的pushInfo字段
        //$template ->set_pushInfo($actionLocKey,$badge,$message,$sound,$payload,$locKey,$locArgs,$launchImage);
        //$template ->set_pushInfo("test",1,"message","","","","","");
        return $template;
	}

	protected function IGtTransmissionTemplateDemo($msg){
        $template =  new IGtTransmissionTemplate();
        $template->set_appId(GT_APPID);//应用appid
        $template->set_appkey(GT_APPKEY);//应用appkey
        $template->set_transmissionType(1);//透传消息类型
        $template->set_transmissionContent($msg);//透传内容
	//iOS推送需要设置的pushInfo字段
	//$template ->set_pushInfo($actionLocKey,$badge,$message,$sound,$payload,$locKey,$locArgs,$launchImage);
	//$template ->set_pushInfo("", 0, "", "", "", "", "", "");
        return $template;
	} 
	
	protected function checkSign($parameters, $sign){
		try {
			if (null == PARTNERKEY || "" == PARTNERKEY ) {
				throw new SDKRuntimeException("密钥不能为空！" . "<br>");
			}
			$ptemp = array();
			foreach($parameters as $parameter=>$parameterValue){
				if($parameterValue!='' && $parameter!='sign'){
					$ptemp[\Home\Common\Weixin\Pay\CommonUtil::trimString($parameter)] = \Home\Common\Weixin\Pay\CommonUtil::trimString($parameterValue);
				}
			}
			$commonUtil = new \Home\Common\Weixin\Pay\CommonUtil();
			ksort($ptemp);
			$unSignParaString = $commonUtil->formatQueryParaMap($ptemp, false);
			$md5SignUtil = new \Home\Common\Weixin\Pay\MD5SignUtil();
			$mysign = $md5SignUtil->sign($unSignParaString,$commonUtil->trimString(PARTNERKEY));
			if($mysign == $sign){
				return true;
			}
			return false;
		}catch (SDKRuntimeException $e)
		{
			die($e->errorMessage());
		}

	}
	
	protected function checkBizSign($nativeObj, $sign){
		$wxPayHelper = new \Home\Common\Weixin\Pay\WxPayHelper();
		$mysign = $wxPayHelper->get_biz_sign($nativeObj);
		if($mysign == $sign){
			return true;
		}
		return false;
	}
	
	public function testSign(){
		/**/
		$sign = "908BB68D9C6DC82EF0877A91EAD40792";
		$parameters = array('sign_type'=>'MD5','input_charset'=>'UTF-8',"bank_billno"=>"201501276132442103","bank_type"=>"2011","discount"=>"0","fee_type"=>"1","notify_id"=>"0PRAX7awQjsfvE0jZktMdMTotGwtKBgW2wgF9uqHMZACokHFsnWi3DSUBN3tfw1-A06znTU8bRxR2PpN4zuxbNX1NAxIEy2D","out_trade_no"=>"92","partner"=>"1220503701","product_fee"=>"1","time_end"=>"20150127111801","total_fee"=>"1","trade_mode"=>"1","trade_state"=>"0","transaction_id"=>"1220503701201501276028125413","transport_fee"=>"0");
		if($this->checkSign($parameters, $sign)){
			echo 'success';
		}else{
			echo 'error';
		}
		
		//echo md5('bank_billno=201501276132442103&bank_type=2011&discount=0&fee_type=1&input_charset=UTF-8&notify_id=0PRAX7awQjsfvE0jZktMdMTotGwtKBgW2wgF9uqHMZACokHFsnWi3DSUBN3tfw1-A06znTU8bRxR2PpN4zuxbNX1NAxIEy2D&out_trade_no=92&partner=1220503701&product_fee=1&sign_type=MD5&time_end=20150127111801&total_fee=1&trade_mode=1&trade_state=0&transaction_id=1220503701201501276028125413&transport_fee=0&key='.PARTNERKEY);
	}

	/*
	 * @desc 处理微信支付成功
	*/
	protected function doOrderDone($isIn){
		//检查weixin参数
		$sign = $_GET['sign'];
		if(!$sign || !$this->checkSign($_GET, $sign)){
			return;
		}
		//写log
		$wxlog = M('payment_wx_log');
		$out_trade_no = $_GET['out_trade_no'];
		if(!$out_trade_no){
			return;
		}
		if($wxlog->where(array('out_trade_no'=>$out_trade_no))->getField('out_trade_no')){//已存在纪录
			echo 'success';
			return;
		}
		//获取log数据
		$getdata = null;
		foreach ($_GET as $key=>$value){
			if($getdata == null){
				$getdata = $key.'='.$value;
			}else{
				$getdata .= '&'.$key.'='.$value;
			}
		}
		$postdata = $GLOBALS["HTTP_RAW_POST_DATA"];
    $wxlog->add(array('out_trade_no'=>$out_trade_no, 'getdata'=>$getdata, 'postdata'=>$postdata));//日志
    
    //处理订单逻辑
    $payment_record = M('payment_record');
    $park_order = M('park_order');
    $oid = $payment_record->where(array('id'=>$out_trade_no))->getField('oid');
    $parkid = $park_order->where(array('id'=>$oid))->getField('pid');
		if($isIn){
			$payment_record->where(array('id'=>$out_trade_no))->save(array('state'=>1));
			$park_order->where(array('id'=>$oid,'state'=>-1))->save(array('state'=>0,'startime'=>time()));
		}else{
			$payment_record->where(array('id'=>$out_trade_no))->save(array('state'=>1));
			$park_order->where(array('id'=>$oid))->save(array('state'=>2,'endtime'=>time()));
		}
		
		/*推送*/
		$fee = $_GET['total_fee']/100;
		$msg = json_encode(array('t'=>$isIn?'in':'out'));
		$title = $isIn?"嘟嘟停车：收到预付订单".$fee."元！":"嘟嘟停车：收到停车费".$fee."元！";
		$txt = $isIn?"车主已预付，注意请放行入库":"车主已付款，注意请放行出库";
		$igt = new IGeTui(GT_HOST,GT_APPKEY,GT_MASTERSECRET);
		//接收方
		$cids = array('cbb4eaa0824d4b4b28cb5ba267dba9ed','7f1cbe039539576448ee0e7b0a78b7ad','7e15f5387abc091893d62420ae56ab52');
		$targetList = array();
		foreach($cids as $cid){
			$target1 = new IGtTarget();
			$target1->set_appId(GT_APPID);
			$target1->set_clientId($cid);
		
			$targetList[] = $target1;
		}
		//个推popup消息
		$template = $this->IGtNotificationTemplateDemo($title, $txt, $msg);
		$message = new IGtListMessage();
		$message->set_isOffline(true);//是否离线
		$message->set_offlineExpireTime(3600*12*1000);//离线时间
		$message->set_data($template);//设置推送消息类型
		//$message->set_PushNetWorkType(0);	//设置是否根据WIFI推送消息，1为wifi推送，0为不限制推送
		$contentId = $igt->getContentId($message);
		$rep = $igt->pushMessageToList($contentId, $targetList);
		//var_dump($rep);
		//echo "<br><br>";
		//个推透传消息
		$template2 = $this->IGtTransmissionTemplateDemo($msg);
		$message2 = new IGtListMessage();
		$message2->set_isOffline(true);//是否离线
		$message2->set_offlineExpireTime(3600*12*1000);//离线时间
		$message2->set_data($template2);//设置推送消息类型
		//$message->set_PushNetWorkType(0);	//设置是否根据WIFI推送消息，1为wifi推送，0为不限制推送
		$contentId = $igt->getContentId($message2);
		$rep = $igt->pushMessageToList($contentId, $targetList);
		//var_dump($rep);
		//echo "<br><br>";
		echo 'success';
	}
	
	/*
	 * @desc 预付成功，微信调用的回调函数
	*/
	public function genOrderDone(){
		return $this->doOrderDone(true);
	}

	/*
	 * @desc 车费结算付款成功微信的回调接口
	*/

	public  function checkOutDone(){
		return $this->doOrderDone(false);
	}


    //test
    public function parkingFeeTest($parkid, $min = 30, $hour = 10, $year = 2015, $month = 1, $day = 23){
    	
    	echo("<b>".$year."年".$month."月".$day."日".$hour."点 汽车进场：</b><br>");
  		$startTime = mktime($hour,0,0,$month,$day,$year);
  		for($hours = 0; $hours < 24; $hours++){
  			$endTime = $min*60;
  			while($endTime < 3600){
  				$fee = $this->_parkingFee($startTime, $startTime+$hours*3600+$endTime, $parkid);
  				echo("停".$hours."小时".($endTime/60)."分钟收费".$fee."元，");
  				$endTime += $min*60;
  			}
  			$fee = $this->_parkingFee($startTime, $startTime+($hours+1)*3600, $parkid);
  			echo("停".($hours+1)."小时收费".$fee."元。<br>");
  		}
    		
    	/*
    	$rulesmoney = M('rules_money');
    	$con2 = "rulesid=2";
			$moneyArr = $rulesmoney->where($con2)->order('mins')->select();
			dump($moneyArr);
			*/
			
			return 0;
    }
    public function parkingFee($startTime, $parkid){
    	$fee = $this->_parkingFee($startTime, time(), $parkid);
    	
    	return $fee;
    }
    //实际计算方法，增加$endTime参数便于测试
    protected function _parkingFee($startTime, $endTime, $parkid){
				$fee = 0;
				$rulestime = M('rules_time');
				$rulesmoney = M('rules_money');
				while($startTime < $endTime){
					$timeStr = date("H:i:s",$startTime);
					//找到开始停车那个时间点所适用规则
					$con1 = "parkid=".$parkid." and startime<='".$timeStr."' and endtime>='".$timeStr."'";
					$ruleid = $rulestime->where($con1)->getField('id');
					if(!$ruleid){//没有合适的规则
						break;
					}
					//根据停车时长计算费用
					$mins = ceil(($endTime-$startTime)/60);
					$con2 = "rulesid=".$ruleid;
					$moneyArr = $rulesmoney->where($con2)->order('mins')->select();
					$arrLength = count($moneyArr);
					$money=0;
					for($i=0;$i < $arrLength;$i++){
						if($moneyArr[$i]['mins']>=$mins){
							$money=$moneyArr[$i]['money'];
							break;
						}
					}
					if($i >= $arrLength){//超过规则所支持的时长，需要用最长所支持的时间
						$money = $moneyArr[$arrLength-1]['money'];
						$mins = $moneyArr[$arrLength-1]['mins'];
					}
					$fee += $money;
					$startTime += $mins*60;
					/*if($mins <= 0){
						dump($moneyArr);
						break;
					}*/
				}
				
        return $fee;
    }
}