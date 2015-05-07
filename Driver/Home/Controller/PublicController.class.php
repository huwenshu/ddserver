<?php
require_once(dirname(__FILE__) . '/../Common/Weixin/Pay/' . 'WxPay.config.php');


/**
 * Driver公关页面控制器
 * @Bin
 */
class PublicController extends BaseController {

    /**
     * 用户登录
     */
    public function login($phone = null){
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
            //$arr['carid'] = $carid;
            $arr['createtime'] = date('Y-m-d H:i:s');
            $uid = $Driver->add($arr);
        }
        $uuid = $this->createUUID($uid);
        $temp = array('uid' => $uid, 'uuid' =>$uuid);
        $this->ajaxOk($temp);
    }

	/**
	 * 微信用户登录
	 */
	public function wxlogin($openid=null, $phone = null){

		$Driver = M('DriverInfo');

		$map = array('openid' => $openid);
		$data = $Driver->where($map)->find();
		if(!empty($data)){//openid已经存在,先解除绑定
			$data['openid'] = null;
            $Driver->where($map)->save($data);
            $uid = $this->_wxlogin($openid,$phone);
		}
		else{//openid不存在
            $uid = $this->_wxlogin($openid,$phone);
		}


		$uuid = $this->createUUID($uid);
		$temp = array('uid' => $uid, 'uuid' =>$uuid);
		$this->ajaxOk($temp);
	}

    //数据库里面还没有这个openid，采取登录方式

    protected  function _wxlogin($openid, $phone){

        $Driver = M('DriverInfo');
        $map = array('telephone' => $phone);
        $data = $Driver->where($map)->find();

        if(!empty($data)){//电话号码已经存在
            $uid = $data['id'];
            $map = array('id' => $uid);
            $temp['openid'] = $openid;
            $temp['updater'] = $uid;
            $Driver->where($map)->save($temp);
        }
        else{
            $arr['openid'] = $openid;
            $arr['telephone'] = $phone;
            $arr['createtime'] = date('Y-m-d H:i:s');
            $uid = $Driver->add($arr);
        }

        return $uid;
    }

    public function checkLogin($uid, $uuid){
        $data = $this->getUsercache($uuid);
        if(!empty($data)){
            if ($data['uid'] == $uid) {
                $this->ajaxOk('');
            }
            else{
                $this->ajaxFail();
				//$this->ajaxMsg('uuid无效,uid'.$uid);
            }
        }
        else{
            $this->ajaxFail();
			//$this->ajaxMsg('uid无效');
        }

    }

	//todo
	//用于测试登陆
	public function checkUid($uid){
		$data = $this->getUsercache($uid);
		if(!empty($data)){
			$this->ajaxOk('uuid:'.$data['uuid']);
		}
		else{
			$this->ajaxFail();
			//$this->ajaxMsg('uuid不存在!');
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
        include_once(dirname(__FILE__) . '/../Common/Weixin/WxPay/' . 'WxPayPubHelper.php');
        $notify = new Notify_pub();

        //存储微信的回调
        $xml = $GLOBALS['HTTP_RAW_POST_DATA'];
        $notify->saveData($xml);

        //验证签名，并回应微信。
        //对后台通知交互时，如果微信收到商户的应答不是成功或超时，微信认为通知失败，
        //微信会通过一定的策略（如30分钟共8次）定期重新发起通知，
        //尽可能提高通知的成功率，但微信不保证通知最终能成功。
        if($notify->checkSign() == FALSE){
            $notify->setReturnParameter("return_code","FAIL");//返回状态码
            $notify->setReturnParameter("return_msg","签名失败");//返回信息
        }else{
            $notify->setReturnParameter("return_code","SUCCESS");//设置返回码
        }
        $returnXml = $notify->returnXml();
        echo $returnXml;

//		//检查weixin参数
//		$sign = $_GET['sign'];
//		if(!$sign || !$this->checkSign($_GET, $sign)){
//			return;
//		}
		//写log
		$wxlog = M('payment_wx_log');
		$trade_no = $notify->data["out_trade_no"] ;
		if(!$trade_no){
			return;
		}
		$out_trade_no = substr($trade_no,14); //截取付款号，去除时间戳
		if($wxlog->where(array('out_trade_no'=>$out_trade_no))->getField('out_trade_no')){//已存在纪录
			//echo 'success';
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
		$postdata = $xml;
    $wxlog->add(array('out_trade_no'=>$out_trade_no, 'getdata'=>$getdata, 'postdata'=>$postdata));//日志
    
    //处理订单逻辑
    $payment_record = M('payment_record');
    $park_order = M('park_order');
    $prdata = $payment_record->where(array('id'=>$out_trade_no))->limit(1)->select();
    if(!$prdata || count($prdata) == 0){//订单不存在
			return;
		}
		//处理折扣劵
		if($prdata[0]['cid'] > 0){
			$this->_consumeCoupon($prdata[0]['cid']);
		}
		//修改订单状态
    $oid = $prdata[0]['oid'];
    $park_order_data = $park_order->where(array('id'=>$oid))->find();
    $parkid = $park_order_data['pid'];
    $uid = $park_order_data['uid'];
    $now = time();
		if($isIn){
			$payment_record->where(array('id'=>$out_trade_no))->save(array('state'=>1));
			$endtime = $this->_parkingEndTime($now, $now+100, $parkid);
			$park_order->where(array('id'=>$oid,'state'=>-1))->save(array('state'=>0,'startime'=>date("Y-m-d H:i:s", $now),'endtime'=>date("Y-m-d H:i:s", $endtime)));
		}else{
			$payment_record->where(array('id'=>$out_trade_no))->save(array('state'=>1));
			$starttime = strtotime($park_order_data['startime']);
			$endtime = $this->_parkingEndTime($starttime, $now, $parkid);
			$park_order->where(array('id'=>$oid))->save(array('state'=>2,'endtime'=>date("Y-m-d H:i:s", $endtime)));
		}

        //-本次付费的钱
        $Payment = M('PaymentRecord');
        $map = array();
        $map['id'] = $out_trade_no;
        $pay = $Payment->where($map)->find();
        $change = $pay['money'];
        $note = $pay['id'];
				//账户余额
        $ParkInfo = M('ParkInfo');
        $map['id'] = $parkid;
        $balance = $ParkInfo->where($map)->getField('balance');
        $ParkInfo->where($map)->setInc('balance',$change); //账户余额更新
        $newMoney = $balance + $change;

        /*记录金钱变化到CSV文件*/
        $msgs = array();
        $msgs['ip'] = $_SERVER['REMOTE_ADDR'];//用户ip
        $msgs['parkid'] = $parkid;//停车场编号
        $msgs['uid'] = $uid;//操作者id
        $msgs['opt'] = 6;//6-用户付费记录
        $msgs['oldValue'] = $balance;//原值
        $msgs['newValue'] = $newMoney;//新值
        $msgs['change'] = $change;//获得积分
        $msgs['note'] = $note ;//补充信息

        takeCSV($msgs);

		/*推送*/
		$this->getuiPush($parkid, $isIn, $isIn?"嘟嘟停车：您收到新的订单！":"嘟嘟停车：您收到新的付款！", $isIn?"车主已预付，注意请放行入库":"车主已付款，注意请放行出库");

        //发送Email
        $parkName = $this->getParkName($parkid);
        $carid = $park_order_data['carid'];
        $telephone = $this->getDriver($uid)['telephone'];
        $money = $change;
        $parkorder = $park_order->where(array('id'=>$oid))->find();
        if($isIn){
            $stateStr = "预付";
            $starttimeStr = "<br/>下单时间：".$parkorder['startime'];
            $endtimeStr = "<br/>截止时间：".$parkorder['endtime'];
            $entrytimeStr = "";
        }
        else{
            $stateStr = "结算";
            $starttimeStr = "<br/>下单时间：".$parkorder['startime'];
            $endtimeStr = "<br/>截止时间：".$parkorder['endtime'];
            $entrytimeStr = "<br/>进场时间：".$parkorder['entrytime'];
        }

        $title = '[用户订单-'.$stateStr.']';
        $content = '停车场：'.$parkName.'<br>车牌：'.$carid.
            '<br>车主电话：'.$telephone.'<br>订单状态：'.$stateStr.'<br>付费金额：'.$money.$starttimeStr.$endtimeStr.$entrytimeStr.'<br>订单号：'.$oid;

        $map = array();
        $map['id'] = $parkid;
        $status = $ParkInfo->where($map)->getField('status');
        if($status == 1 ){
            $send = $this->sendEmail('all@duduche.me', $title, $content);
        }
	}

	/*
	 * @desc 预付成功，微信调用的回调函数
	*/
	public function genOrderDone(){
        $this->doOrderDone(true);
        $this->_exit();
	}
	/*
	 * @desc 车费结算付款成功微信的回调接口
	*/
	public  function checkOutDone(){
        $this->doOrderDone(false);
        $this->_exit();
	}
	public function parkingTimeTest($parkid, $mins){
		$now = time();
		$endtime = $this->_parkingEndTime($now, $now + $mins*60, $parkid,false);
		echo $endtime;
		echo "<br>";
		echo date("Y-m-d H:i:s",$endtime);
	}
	//test
	public function parkingFeeTest($parkid, $starttime, $endtime, $isdebug=1){
		
		$start = strtotime(urldecode($starttime));
		$end = strtotime(urldecode($endtime));
		if($isdebug){
			echo urldecode($starttime).'('.$start.'),'.urldecode($endtime).'('.$end.')<br>';
		}
		$fee = $this->_parkingFee($start, $end, $parkid,$isdebug);

		if($isdebug){
			echo $fee;
		}else{
			$result=array('fee'=>$fee);
			$this->ajaxOk($result);
		}
	}

	/**
	 *  @desc 判断是否具有某项权限
	 *  @param int $per 权限判断值
	 *  @param int $base 权限比较值
	 */
	protected function perCompare($per, $base){
		if(($per&$base) > 0){
			return true;
		}
		else{
			return false;
		}
	}
	
	public  function  getOpenArea($city='sh'){
		include_once(dirname(__FILE__) . '/../Conf/' . 'config_open_area.php');
		
		$result=array('area'=>$config_open_area[$city]);
		$this->ajaxOk($result);
	}
	
	/*
	 * @desc 检查红包状态
	*/
	public  function  checkGiftPack($code,$uid=0,$fromid=0){
		//log it
		$this->_saveGiftLog($code, 0, $uid, $fromid);
		
		$gpArr = $this->_checkGiftPack($code,$uid);
		if(is_array($gpArr)){
			$result = array();
			$result['gift'] = array('t'=>$gpArr['type'],'e'=>$gpArr['endtime']);
			$this->ajaxOk($result);
		}else{
			//0				没有合适的红包
			//-1			已领完
			//-2			活动还没开始
			//-3			活动已结束
			if($gpArr == 0){
				$this->ajaxMsg("红包不存在，或您无法领取该红包");
			}else if($gpArr == -1){
				$this->ajaxMsg("该红包已被领完，谢谢！");
			}else if($gpArr == -2){
				$this->ajaxMsg("该红包活动尚未开始，敬请期待！");
			}else if($gpArr == -3){
				$this->ajaxMsg("该红包已过期，谢谢！");
			}else if($gpArr == -4){
				$this->ajaxMsg("您已领取过该红包，谢谢！");
			}
		}
	}

    /*
	 * @desc 微信告警
	*/
    public function alarm(){
        $postStr = $GLOBALS["HTTP_RAW_POST_DATA"];
        $postObj = simplexml_load_string($postStr, 'SimpleXMLElement', LIBXML_NOCDATA);

        $AlarmLog = M('AlarmLog');
        $data = array();
        $data['appid'] = $postObj->AppId;
        $data['errortype'] = $postObj->ErrorType;
        $data['description'] = $postObj->Description;
        $data['alarmcontent'] = $postObj->AlarmContent;
        $data['timestamp'] = $postObj->TimeStamp;
        $AlarmLog->add($data);

        echo 'success';


    }
	//测试区
	public function testCreateGiftPack(){
		print_r($this->_createGiftPack(0, 0, date("Y-m-d H:i:s"), date("Y-m-d H:i:s",time()+3600), date("Y-m-d H:i:s",time()), date("Y-m-d H:i:s",time()+7200), 1, rand(2,5), 100));
	}
	public function testCreateCoupon(){
		print_r($this->_createCoupon(1, 0, 1, date("Y-m-d H:i:s"), date("Y-m-d H:i:s",time()+3600), 0));
	}
	public function testCreateCoupon1(){
		print_r($this->_createCoupon1(1, date("Y-m-d H:i:s"), date("Y-m-d H:i:s",time()+3600)));
	}
	public function testUseGiftPack($code){
		print_r($this->_useGiftPack(1, $code));
	}
	public function testListCoupon(){
		print_r($this->_listCoupon(1));
	}
	public function testUseCoupon($id){
		print_r($this->_useCoupon(1, $id, 10));
	}
	
	public function testEnter($pid){
		$oid = $this->simulateEnter(0, $pid, 0, true);
		$result=array('oid'=>$oid);
		$this->ajaxOk($result);
	}
	public function testLeave($oid){
		$this->simulateLeave($oid, time()+3600, true);
		$result=array();
		$this->ajaxOk($result);
	}
	public function testGame($pid){
		echo $this->simulateEnter(-1, $pid, 0, true);
		echo "<br>done!";
	}
	
	public function open_wx_map_html($lat, $lng, $name, $addr, $infourl = ''){
		$protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' || $_SERVER['SERVER_PORT'] == 443) ? "https://" : "http://";
    $url = "$protocol$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";
		$signPackage = $this->GetSignPackage($url);
?>
<!DOCTYPE html>
<html>
<head>
  <meta charset="UTF-8">
  <title></title>
</head>
<body>
</body>
<script src="http://res.wx.qq.com/open/js/jweixin-1.0.0.js"></script>
<script>
  wx.config({
    debug: true,
    appId: '<?php echo $signPackage["appId"];?>',
    timestamp: <?php echo $signPackage["timestamp"];?>,
    nonceStr: '<?php echo $signPackage["nonceStr"];?>',
    signature: '<?php echo $signPackage["signature"];?>',
    jsApiList: [
      'checkJsApi',
      'openLocation'
    ]
  });
  wx.ready(function () {
    wx.openLocation({
		    latitude: <?php echo $lat;?>,
		    longitude: <?php echo $lng;?>,
		    name: '<?php echo $name;?>',
		    address: '<?php echo $addr;?>',
		    scale: 16,
		    infoUrl: '<?php echo $infourl;?>'
		});
  });
</script>
</html>
<?php
		print_r($signPackage);
	}
	//测试区
}