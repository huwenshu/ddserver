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
		//检查weixin参数
		$sign = $_GET['sign'];
		if(!$sign || !$this->checkSign($_GET, $sign)){
			return;
		}
		//写log
		$wxlog = M('payment_wx_log');
		$trade_no = $_GET['out_trade_no'];
		if(!$trade_no){
			return;
		}
		$out_trade_no = substr($trade_no,14); //截取付款号，去除时间戳
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

        //账户余额
        $ParkInfo = M('ParkInfo');
        $map['id'] = $parkid;
        $parkData = $ParkInfo->where($map)->find();
        $balance = $parkData['balance'];
        //-本次付费的钱
        $Payment = M('PaymentRecord');
        $map = array();
        $map['id'] = $out_trade_no;
        $pay = $Payment->where($map)->find();
        $change = $pay['money'];
        $note = $pay['id'];

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
		$fee = $_GET['total_fee']/100;
		$msg = json_encode(array('t'=>$isIn?'in':'out'));
		$title = $isIn?"嘟嘟停车：收到预付订单".$fee."元！":"嘟嘟停车：收到停车费".$fee."元！";
		$txt = $isIn?"车主已预付，注意请放行入库":"车主已付款，注意请放行出库";
		$igt = new IGeTui(GT_HOST,GT_APPKEY,GT_MASTERSECRET);
		//接收方
		//$cids = array('cbb4eaa0824d4b4b28cb5ba267dba9ed','7f1cbe039539576448ee0e7b0a78b7ad','7e15f5387abc091893d62420ae56ab52');
		$cids = $this->getPushIds($parkid, $isIn);
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
	public function parkingTimeTest($parkid, $mins){
		$now = time();
		$endtime = $this->_parkingEndTime($now, $now + $mins*60, $parkid);
		echo $endtime;
		echo "<br>";
		echo date("Y-m-d H:i:s",$endtime);
	}
	//test
	public function parkingFeeTest($parkid, $starttime, $endtime){
		
		echo urldecode($starttime).','.urldecode($endtime).'<br>';
		
		$fee = $this->_parkingFee(strtotime(urldecode($starttime)), strtotime(urldecode($endtime)), $parkid);

		echo $fee;
	}

	/**
	 *  @desc 获取通知的Pushid接口
	 *  @param int $pid 停车场id
	 *  @param boolean $type 通知阶段 true-预付完成 false-结算完成
	 */
	protected function getPushIds($pid, $type)
	{
		$Park = M('ParkInfo');
		$map = array();
		$map['id'] = $pid;
		$parkData = $Park->where($map)->find();

		if(empty($parkData)){
			return null;
		}
		else{
			$shortname = $parkData['shortname'];
		}

		$ParkAdmin = M('ParkAdmin');
		$map = array();
		$map['parkname'] = $shortname;
		$adminData = $ParkAdmin->where($map)->select();

		$result = array();
		if(empty($adminData)){
			return null;
		}
		else{
			foreach($adminData as $key => $value){
				if($type){
					if($this->perCompare($value['jobfunction'], 1)){
						$result[] = $value['pushid'];
					}
				}
				else{
					if($this->perCompare($value['jobfunction'], 2)){
						$result[] = $value['pushid'];
					}
				}
			}
		}
		return $result;
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
	
	//测试区
	public function testCreateGiftPack(){
		print_r($this->_createGiftPack(0, 0, date("Y-m-d H:i:s"), date("Y-m-d H:i:s",time()+3600), date("Y-m-d H:i:s",time()+60), date("Y-m-d H:i:s",time()+7200), 1, rand(2,5), 1));
	}
	public function testCreateCoupon(){
		print_r($this->_createCoupon(0, 0, 1, date("Y-m-d H:i:s"), date("Y-m-d H:i:s",time()+3600), 0));
	}
	public function testCreateCoupon1(){
		print_r($this->_createCoupon1(0, date("Y-m-d H:i:s"), date("Y-m-d H:i:s",time()+3600)));
	}
	public function testUseGiftPack($code){
		print_r($this->_useGiftPack(0, $code));
	}
	public function testListCoupon(){
		print_r($this->_listCoupon(0));
	}
	public function testUseCoupon($id){
		print_r($this->_useCoupon(0, $id, 10));
	}
	//测试区
}