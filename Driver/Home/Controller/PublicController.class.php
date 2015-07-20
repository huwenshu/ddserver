<?php

/**
 * Driver公关页面控制器
 * @Bin
 */
class PublicController extends BaseController {
    
    private $uid;
    
    public function _initialize(){
        parent::_initialize();
        
        $uid = I('get.uid');
        $uuid = I('get.uuid');
        $this->uid = $uid;
    }

    /**
     * 用户登录
     */
    public function login($phone = null, $env = null, $carid = null){
        $uid=null;

        $Driver = M('DriverInfo');
        $map = array('telephone' => $phone);
        $data = $Driver->where($map)->find();

        if(!empty($data)){
            $uid = $data['id'];
            if($env){
                $map = array('id'=>$uid);
                $temp = array('env'=>$env);
                $Driver->where($map)->save($temp);
            }
        }
        else{
            $arr['telephone'] = $phone;
            //$arr['carid'] = $carid;
            $arr['createtime'] = date('Y-m-d H:i:s');
            if($env){
                $arr['env'] = $env;
            }
            $uid = $Driver->add($arr);
            if($carid){
            $DriverCar = M('DriverCar');
            //保存数据
            $temp = array();
            $temp['driverid'] = $uid;
            $temp['carid'] = $carid;
            $temp['status'] = 1;
            $temp['creater'] = $uid;
            $temp['createtime'] = date("Y-m-d H:i:s",time());
            $temp['updater'] = $uid;
            $DriverCar->add($temp);
            }
        }
        $uuid = $this->createUUID($uid);
        $temp = array('uid' => $uid, 'uuid' =>$uuid);
        $this->ajaxOk($temp);
    }

	/**
	 * 微信用户登录
	 */
	public function wxlogin($openid=null, $phone = null, $carid = null){

		$Driver = M('DriverInfo');

		$map = array('openid' => $openid);
		$data = $Driver->where($map)->find();
		if(!empty($data)){//openid已经存在,先解除绑定
			$data['openid'] = null;
            $Driver->where($map)->save($data);
            $uid = $this->_wxlogin($openid,$phone,$carid);
		}
		else{//openid不存在
            $uid = $this->_wxlogin($openid,$phone,$carid);
		}


		$uuid = $this->createUUID($uid);
		$temp = array('uid' => $uid, 'uuid' =>$uuid);
		$this->ajaxOk($temp);
	}

    //数据库里面还没有这个openid，采取登录方式

    protected  function _wxlogin($openid, $phone, $carid){

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
            if($carid){
                $DriverCar = M('DriverCar');
                //保存数据
                $temp = array();
                $temp['driverid'] = $uid;
                $temp['carid'] = $carid;
                $temp['status'] = 1;
                $temp['creater'] = $uid;
                $temp['createtime'] = date("Y-m-d H:i:s",time());
                $temp['updater'] = $uid;
                $DriverCar->add($temp);
            }
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
        $cost = $park_order_data['cost'];
        $cost = $cost + $prdata[0]['money'];

        $now = time();
		if($isIn){
			include_once(dirname(__FILE__) . '/../Conf/' . 'config_biz.php');
			$now = $now+$config_order_wait_sesc;
			$payment_record->where(array('id'=>$out_trade_no))->save(array('state'=>1));
			$endtime = $this->_parkingEndTime($now, $now+100, $parkid);
			$park_order->where(array('id'=>$oid,'state'=>-1))->save(array('state'=>0, 'cost'=>$cost, 'startime'=>date("Y-m-d H:i:s", $now),'endtime'=>date("Y-m-d H:i:s", $endtime)));
		}else{
			$payment_record->where(array('id'=>$out_trade_no))->save(array('state'=>1));
			$starttime = strtotime($park_order_data['startime']);
            $map = array('oid' => $oid, 'state'=>1);
            $fee = $payment_record->where($map)->sum('money');
			$endtime = $this->_parkingEndTime2($starttime, $fee, $parkid);
			if($park_order_data['state'] == 1){//状态为1的时候，切换到2，其他情况下状态值不变
				$park_order->where(array('id'=>$oid))->save(array('state'=>2, 'cost'=>$cost, 'endtime'=>date("Y-m-d H:i:s", $endtime)));
			}
			else{
				$park_order->where(array('id'=>$oid))->save(array('cost'=>$cost, 'endtime'=>date("Y-m-d H:i:s", $endtime)));
			}
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

		/*发送消息模板给用户的公共号*/
		if($isIn) {
			$openid = $this->getOpenID($uid);
			$parkname = $this->getParkName($parkid);
			$money_r = $pay['money_r'];
			if ($money_r < $change) {
				$payStr = round($change, 2) . "元，其中优惠券抵用" . round($change - $money_r, 2) . "元";
			} else {
				$payStr = round($change, 2) . "元";
			}
			$orderTime = date("Y-m-d H:i:s", $now - $config_order_wait_sesc);

			$msg_json = sprintf(C('NOTICE_TPL_PRE'), $openid, C('TEMPLATE_ID_PRE'), C('TEMPLATE_REDIRECT_URL_PRE'), '恭喜你已预订车位成功！\n', $parkname, $payStr, $orderTime, '\n系统将最晚在15分钟后开始计费，请尽快驶达。\n如对停车场不熟，请点击详情查看停车场的“入口指示”和导航，或联系停车场管理员。');
			$result = $this->noticeMsg($msg_json);
			$result_array = json_decode($result,TRUE);
			if($result_array['errcode'] !=0){
				$this->sendEmail('dubin@duduche.me', "预定模板消息发送失败", "订单号OID：$oid");
			}
		}

        //发送Email
        $parkName = $this->getParkName($parkid);
        $carid = $park_order_data['carid'];
        $telephone = $this->getDriver($uid)['telephone'];
        $money = $change;
        if($isIn){
            $stateStr = "预付";
            $starttimeStr = "<br/>下单时间：".date("Y-m-d H:i:s", $now-$config_order_wait_sesc);
            $endtimeStr = "<br/>截止时间：".date("Y-m-d H:i:s", $endtime);
            $entrytimeStr = "";
        }
        else{
            $stateStr = "结算";
            $starttimeStr = "<br/>开始时间：".$park_order_data['startime'];
            $endtimeStr = "<br/>截止时间：".date("Y-m-d H:i:s", $endtime);
            $entrytimeStr = "<br/>进场时间：".$park_order_data['entrytime'];
        }

        $title = '[用户订单-'.$stateStr.']';
        $content = '停车场：'.$parkName.'<br>车牌：'.$carid.
            '<br>车主电话：'.$telephone.'<br>订单状态：'.$stateStr.'<br>付费金额：'.$money.$starttimeStr.$endtimeStr.$entrytimeStr.'<br>订单号：'.$oid;

        $map = array();
        $map['id'] = $parkid;
        $status = $ParkInfo->where($map)->getField('status');
        if($status > 3 ){
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
    public function parkingTimeTest2($parkid, $starttime, $fee, $isdebug=1){
        $start = strtotime(urldecode($starttime));
        $endtime = $this->_parkingEndTime2($start, $fee, $parkid,$isdebug);
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
    
    //返回附近停车场接口2
    public function search2($lat,$lng,$curlat=0,$curlng=0,$pushid='',$m=0){
        //CVS记录查询的位置信息
        $msgs = array();
        $msgs['ip'] = $_SERVER['REMOTE_ADDR'];//用户ip
        $msgs['uid'] = $this->uid;//操作者id
        $msgs['curlat'] = $curlat;
        $msgs['curlng'] = $curlng;//当前值
        $msgs['lat'] = $lat;
        $msgs['lng'] = $lng;//新值
        $msgs['pushid'] = $pushid;
        locCSV2($msgs);
        
        $this->lat = $lat;
        $this->lng = $lng;
        $Park = M('ParkInfo');
        $gap = 0.004545 + 0.004545*$m;//0.002727;
        $con = array();
        $con['lat'] = array(array('gt',$lat - $gap),array('lt',$lat + $gap));
        $con['lng'] = array(array('gt',$lng - $gap),array('lt',$lng + $gap));
        
        //HardCode 用于测试
        $openid = $this->getOpenID($this->uid);
        $opens = C('OPENID');
        if(in_array($openid, $opens)){
            $con['status'] = array('EGT', 3);
        }
        else{
            $con['status'] = array('EGT', 4);
        }
        
        $listdata = $Park->where($con)->select();
        usort($listdata, array($this, "status_distance_sort"));	//按距离远近排序
        
        $list = $listdata;
        //$list = array_slice($listdata,0,10,true);//截取前10个停车场
        
        //封装返回值
        $result = array();
        $p = array();
        $porder = null;
        $nowstr = date("Y-m-d H:i:s");
        foreach($list as $key => $value){
            $tmp = array();
            
            //通用信息
            $tmp['id'] = $value['id'];
            $tmp['n'] = $value['name'];
            $tmp['r'] = $value['chargingrules'];
            $tmp['a'] = $value['address'];
            $tmp['b'] = $value['address2'];
            $tmp['i'] = $value['image'];
            if($value['pretype'] && $value['pretype'] != ''){
                $tmp['y'] = str_replace("／","/",$value['pretype']);
            }
            $tmp['lat'] = $value['lat'];
            $tmp['lng'] = $value['lng'];
            $tmp['m'] = $value['spacesum'];
            $tmp['p'] = $value['prepay'];
            
            $style = $value['style'];
            $styleArr = explode('|', $style);
            $styleR = array();
            for($i = 1;$i<count($styleArr)-1; $i++){
                array_push($styleR, C('PARK_STYLE')[$styleArr[$i]]);
            }
            $tmp['t'] = $styleR;//停车场标签
            
            //todo 不对外开放停车场的特殊处理
            if(in_array('不对外开放', $styleR)){
                continue;
            }
            
            //获取停车场当前空位信息 + 下一个车位时间段
            $parkstate = $this->_getParkState($value);
            $tmp['e'] = $parkstate['next'];
            
            //开放时间段
            $tmp['o'] = array($this->isClosedNow($value) ? 0 : 1, $value['startmon'], $value['endmon'], $value['startsat'], $value['endsat']);
            
            if($value['status'] == 4 || $value['status'] == 3){//合作停车场
                $tmp['c'] = 1; //合作停车场设为1
                $tmp['s'] = $value['parkstate'];
            }
            else{//信息化产品
                $tmp['c'] = 0; //信息化设为0
                $tmp['s'] = $parkstate['current'];//信息化停车场的空车位状态根据时段来判断
            }
            $showevent = false;
            //echo $value['id'].'/'.$nowstr.'/'.$value['e_start'].'/'.$value['e_end'].'<br>';
            if($nowstr > $value['e_start'] && $nowstr < $value['e_end']){//活动期间
                if($value['e_t']&1){//只限第一单用户
                    if($porder === null){//需要获取用户下单数量信息
                        $payment = M('ParkOrder');
                        $con2 = array('uid'=>$this->uid,'state'=>array('neq',－1));
                        if($payment->where($con2)->find()){
                            $porder = 1;
                        }else{
                            $porder = 0;
                        }
                    }
                    if($porder === 0){
                        $showevent = true;
                    }
                }else{//全部用户
                    $showevent = true;
                }
            }
            if($showevent){
                $tmp['d'] = array(($value['e_t']&2)?1:0,$value['e_p']);
            }
            
            array_push($p, $tmp);
        }
        $result['p'] = $p;
        
        $ParkFree = M('ParkFreeInfo');
        $con = array();
        $con['lat'] = array(array('gt',$lat - $gap),array('lt',$lat + $gap));
        $con['lng'] = array(array('gt',$lng - $gap),array('lt',$lng + $gap));
        $con['status'] = 1;
        $order = 'id desc';
        $datas = $ParkFree->where($con)->order($order)->select();
        $f = array();
        foreach($datas as $key => $value){
            $tmp = array();
            
            //通用信息
            $tmp['id'] = $value['id'];
            $tmp['n'] = $value['name'];
            $tmp['b'] = $value['dsc'];
            $tmp['i'] = $value['image'];
            $tmp['lat'] = $value['lat'];
            $tmp['lng'] = $value['lng'];
            
            $tmp['t'] = $value['note'];//停车场标签
            $tmp['c'] = 2; //免费设为2
            
            array_push($f, $tmp);
        }
        $result['f'] = $f;
        if(!$datas){//附近无免费
            $order = 'abs(lat-'.$lat.')+abs(lng-'.$lng.') asc';
            $con = array('status' => 1);
            $value = $ParkFree->where($con)->order($order)->find();
            $tmp = array();
            
            //通用信息
            $tmp['id'] = $value['id'];
            $tmp['n'] = $value['name'];
            $tmp['b'] = $value['dsc'];
            $tmp['i'] = $value['image'];
            $tmp['lat'] = $value['lat'];
            $tmp['lng'] = $value['lng'];
            
            $tmp['t'] = $value['note'];//停车场标签
            $tmp['c'] = 2; //免费设为2
            $result['a'] = $tmp;
        }
        
        $e = array();
        $e['c'] =  $this->getDefualtCarid($this->uid);
        $e['u'] = C('PARK_IMG_QINIU').'/Park/';
        $result['e'] = $e;
        
        if(count($result['p']) == 0){
            include_once(dirname(__FILE__) . '/../Conf/' . 'config_open_area.php');
            $this->ajaxOk($result,array('area'=>$config_open_area['sh']));
        }else{
            $this->ajaxOk($result);
        }
        
    }
    
    //按合作状态 + 距离 排序比较函数
    protected function status_distance_sort( &$v1,&$v2){
        $dis1 = $this->getDistance($v1['lat'],$v1['lng'],$this->lat,$this->lng);
        $dis2 = $this->getDistance($v2['lat'],$v2['lng'],$this->lat,$this->lng);
        
        //实惠标记 + 在开放时间段
        $sh1 = ((strpos($v1['style'],'|SH|') !== false) && ($this->isClosedNow($v1) === false)) ? 1:0;
        $sh2 = ((strpos($v2['style'],'|SH|') !== false) && ($this->isClosedNow($v2) === false)) ? 1:0;
        
        //先把合作+信息化的都改成合作。
        $v1['status'] = ($v1['status'] == 14 ? 4 : $v1['status']);
        $v2['status'] = ($v2['status'] == 14 ? 4 : $v2['status']);
        
        //针对合作但是已满，作信息化处理
        if($v1['status'] == 4 && $v1['parkstate'] == 0){
            $v1['status'] = 14;
        }
        if($v2['status'] == 4 && $v2['parkstate'] == 0){
            $v2['status'] = 14;
        }
        
        //针对已合作，但是不在开放时间段的，作信息化处理
        if($v1['status'] == 4 && $this->isClosedNow($v1)){
            $v1['status'] = 14;
        }
        if($v2['status'] == 4 && $this->isClosedNow($v2)){
            $v2['status'] = 14;
        }
        
        
        $arr1 = array(3,4);
        $arr2 = array(10,11,12,13,14);
        
        if(in_array($v1['status'],$arr1)){
            $t1 = 1;
        }
        else{
            $t1 = 2;
        }
        if(in_array($v2['status'],$arr1)){
            $t2 = 1;
        }
        else{
            $t2 = 2;
        }
        
        
        if($t1 < $t2){
            return -1;
        }
        elseif($t1 > $t2){
            return 1;
        }
        else{
            //合作状态相同情况下先按照实惠排序
            if($sh1 < $sh2){
                return 1;
            }else if($sh1 > $sh2){
                return -1;
            }else{
            //再按照距离排序
            if($dis1 < $dis2) {
                return -1;
            } elseif ($dis1 > $dis2)  {
                return 1;
            } else {
                return 0;
            }
            }
        }
        
    }
    
    protected function isClosedNow($parkinfo){
        $nowDay = getdate();
        $startstr=$parkinfo['startsun'];
        $endstr=$parkinfo['endsun'];
        switch($nowDay['wday']){
            case 1:
                $startstr=$parkinfo['startmon'];
                $endstr=$parkinfo['endmon'];
                break;
            case 2:
                $startstr=$parkinfo['starttue'];
                $endstr=$parkinfo['endtue'];
                break;
            case 3:
                $startstr=$parkinfo['startwed'];
                $endstr=$parkinfo['endwed'];
                break;
            case 4:
                $startstr=$parkinfo['startthu'];
                $endstr=$parkinfo['endthu'];
                break;
            case 5:
                $startstr=$parkinfo['startfri'];
                $endstr=$parkinfo['endfri'];
                break;
            case 6:
                $startstr=$parkinfo['startsat'];
                $endstr=$parkinfo['endsat'];
                break;
        }
        $nowstr = date("H:i:s");
        if($startstr <= $nowstr && $nowstr <= $endstr){
            return false;
        }
        else{
            return true;
        }
    }
    
    //获取停车场当前空位信息 + 下一个车位时间段
    /*
     * 时间段列表 00:00:00 ~ t1 ~ t2 ~ s1 ~ s2 ~ 23:59:59, 00:00:00 ~ n1 ~ n2
     * */
    private function _getParkState($value){
        
        $now = getdate();
        if($now['wday'] < 6){
            $freestart = $value['freestartwork'];
            $freeend = $value['freeendwork'];
            $fullstart = $value['fullstartwork'];
            $fullend = $value['fullendwork'];
        }
        else{
            $freestart = $value['freestartweek'];
            $freeend = $value['freeendweek'];
            $fullstart = $value['fullstartweek'];
            $fullend = $value['fullendweek'];
        }
        
        //对于选择了全天满或空状态的特殊处理
        if($freestart == '00:00:00' && $freeend == '24:00:00'){
            $tmp['current'] = 2;
            $tmp['next'] = array(-1, null, null);
            return $tmp;
        }
        
        if($fullstart == '00:00:00' && $fullend == '24:00:00'){
            $tmp['current'] = 0;
            $tmp['next'] = array(-1, null, null);
            return $tmp;
        }
        
        $head = null;
        $freeSet = isset($freestart) && isset($freeend) && $freeend!=$freestart;//开始和结束时间都设置了，并且不能为空，才有效
        $fullSet = isset($fullstart) && isset($fullend) && $fullstart!=$fullend;//开始和结束时间都设置了，并且不能为空，才有效
        
        //采用循环列表来处理时间段问题
        include_once(dirname(__FILE__) . '/../Common/StateCell.php');
        
        if($freeSet && $fullSet){//两个时间段都设置
            $freeCell = new StateCell($freestart, $freeend, 2);
            $head = $freeCell;
            
            if($freeend == $fullstart){
                $fullCell = new StateCell($fullstart, $fullend, 0);
                $freeCell->next = $fullCell;
            }
            else{
                $normalCell1  = new StateCell($freeend, $fullstart, 1);
                $freeCell->next = $normalCell1;
                $fullCell = new StateCell($fullstart, $fullend, 0);
                $normalCell1->next = $fullCell;
            }
            
            if($fullend == $freestart){
                $fullCell->next = $freeCell;
            }
            else{
                $normalCell2  = new StateCell($fullend, $freestart, 1);
                $fullCell->next = $normalCell2;
                $normalCell2->next = $freeCell;
            }
            $currentCell = StateCell::currentCell($head);
            $nextCell = $currentCell->next;
        }
        elseif($freeSet){//只设置空时间段
            $freeCell = new StateCell($freestart, $freeend, 2);
            $head = $freeCell;
            $normalCell = new StateCell($freeend, $freestart, 1);
            $freeCell->next = $normalCell;
            $normalCell->next = $freeCell;
            $currentCell = StateCell::currentCell($head);
            $nextCell = $currentCell->next;
        }
        elseif($fullSet){//只设置满时间段
            $fullCell = new StateCell($fullstart, $fullend, 0);
            $head = $fullCell;
            $normalCell = new StateCell($fullend, $fullstart, 1);
            $fullCell->next = $normalCell;
            $normalCell->next = $fullCell;
            $currentCell = StateCell::currentCell($head);
            $nextCell = $currentCell->next;
        }
        else{//都没有设置
            $currentCell = null;
            $nextCell = null;
        }
        
        $current = $currentCell ? $currentCell->state : -1;
        $next = $nextCell ? array($nextCell->state,$nextCell->starttime,$nextCell->endtime) : array(-1, null, null);
        
        $tmp['current'] = $current;
        $tmp['next'] = $next;
        return $tmp;
    }
    
    //获得［发现］频道信息：免费和活动停车场
    public  function discover(){
        //活动停车场
        $nowstr = date("Y-m-d H:i:s");
        $con = array('e_start'=>array('lt',$nowstr),'e_end'=>array('gt',$nowstr));
        //HardCode 用于测试
        $openid = $this->getOpenID($this->uid);
        $opens = C('OPENID');
        if(in_array($openid, $opens)){
            $con['status'] = array('EGT', 3);
        }
        else{
            $con['status'] = array('EGT', 4);
        }
        //是否老用户?
        $payment = M('ParkOrder');
        $con2 = array('uid'=>$this->uid,'state'=>array('neq',－1));
        if($payment->where($con2)->find()){//老用户
            $con['e_t'] = array('in','0,2');
        }
        $Park = M('ParkInfo');
        $listdata = $Park->where($con)->select();
        $list = $listdata;
        //封装返回值
        $result = array();
        $p = array();
        foreach($list as $key => $value){
            $tmp = array();
            
            //通用信息
            $tmp['id'] = $value['id'];
            $tmp['n'] = $value['name'];
            $tmp['r'] = $value['chargingrules'];
            $tmp['a'] = $value['address'];
            $tmp['b'] = $value['address2'];
            $tmp['i'] = $value['image'];
            if($value['pretype'] && $value['pretype'] != ''){
                $tmp['y'] = str_replace("／","/",$value['pretype']);
            }
            $tmp['lat'] = $value['lat'];
            $tmp['lng'] = $value['lng'];
            $tmp['m'] = $value['spacesum'];
            $tmp['p'] = $value['prepay'];
            
            $style = $value['style'];
            $styleArr = explode('|', $style);
            $styleR = array();
            for($i = 1;$i<count($styleArr)-1; $i++){
                array_push($styleR, C('PARK_STYLE')[$styleArr[$i]]);
            }
            $tmp['t'] = $styleR;//停车场标签
            
            //获取停车场当前空位信息 + 下一个车位时间段
            $parkstate = $this->_getParkState($value);
            
            $tmp['e'] = $parkstate['next'];
            
            //开放时间段
            $tmp['o'] = array($this->isClosedNow($value) ? 0 : 1, $value['startmon'], $value['endmon'], $value['startsat'], $value['endsat']);
            
            if(($value['status'] == 4 || $value['status'] == 3 || $value['status'] == 14 || $value['status'] == 13) && $value['parkstate'] != 0){//合作停车场&&在开放时段&&非满
                $tmp['c'] = 1; //合作停车场设为1
                $tmp['s'] = $value['parkstate'];
            }
            else{//信息化产品
                $tmp['c'] = 0; //信息化设为0
                $tmp['s'] = $parkstate['current'];//信息化停车场的空车位状态根据时段来判断
            }
            
            $tmp['d'] = array(($value['e_t']&2)?1:0,$value['e_p']);
            
            array_push($p, $tmp);
        }
        $result['p'] = $p;
        
        $e = array();
        $e['c'] =  $this->getDefualtCarid($this->uid);
        $e['u'] = C('PARK_IMG_QINIU').'/Park/';
        $result['e'] = $e;
        
        //免费频道
        $freeparks = M('park_free_info');
        $con = array('status'=>1);
        $result['f'] = $freeparks->where($con)->count();
        
        $this->ajaxOk($result);
    }
    
    public function getfreepark($lat, $lng, $province, $city, $district=null, $note=null, $page=0, $max=10, $pushid=''){
        if($page == 0){//do log
            $msgs = array();
            $msgs['ip'] = $_SERVER['REMOTE_ADDR'];//用户ip
            $msgs['uid'] = $this->uid;//操作者id
            $msgs['lat'] = $lat;
            $msgs['lng'] = $lng;//新值
            $msgs['province'] = $province;
            $msgs['city'] = $city;
            $msgs['district'] = $district;
            $msgs['note'] = $note;
            $msgs['pushid'] = $pushid;
            locFreeList($msgs);
        }
        if(!$max){
            $max = 10;
        }
        $ParkFree = M('ParkFreeInfo');
        $con = array('province'=>$province,'city'=>$city,'status'=>1);
        $order = 'abs(lat-'.$lat.')+abs(lng-'.$lng.') asc';
        if($district && $district != ''){
            $con['district'] = $district;
        }
        if($note && $note != ''){
            $con['note'] = array('like','%|'.$note.'|%');
        }
        $page = intval($page);
        $limit = ''.($page*$max).','.($max+1);
        $datas = $ParkFree->where($con)->order($order)->limit($limit)->select();
        //print_r($con);
        
        $result = array('m'=>(count($datas)==$max+1?1:0));
        $p = array();
        foreach($datas as $key => $value){
            $tmp = array();
            
            //通用信息
            $tmp['id'] = $value['id'];
            $tmp['n'] = $value['name'];
            $tmp['b'] = $value['dsc'];
            $tmp['i'] = $value['image'];
            $tmp['lat'] = $value['lat'];
            $tmp['lng'] = $value['lng'];
            
            $tmp['t'] = $value['note'];//停车场标签
            $tmp['c'] = 2; //免费设为2
            
            array_push($p, $tmp);
            
            if(count($p) == $max){
                break;
            }
        }
        $result['p'] = $p;
        
        $e = array();
        $e['u'] = C('PARK_IMG_QINIU').'/Park/';
        $result['e'] = $e;
        
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
			$result['gift'] = array('t'=>$gpArr['type'],'e'=>$gpArr['endtime'],'m'=>array(intval($gpArr['minmoney']),intval($gpArr['maxmoney'])));
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
    /*
	 * @desc 微信下载APP链接
	*/
    public function wx_app_down(){
        $agent = $_SERVER['HTTP_USER_AGENT'];
        if (stristr($agent, 'Android')) {
            header("Location:http://a.app.qq.com/o/simple.jsp?pkgname=com.parking.driverApp");
        } else {
            header("Location:http://duduche.me");
        }
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

	public function testMsg(){
		/*发送消息模板给用户的公共号*/
		$msg_json =  sprintf ( C('NOTICE_TPL_PRE'), 'oMjtxuH5YZ_6TSkGGLUWvW64aiHQ', C('TEMPLATE_ID_PRE'), "http://www.baidu.com", '恭喜你已预订车位成功！', '金沙江停车场','20.0元','2014年9月16日','系统将最晚在15分钟后开始计费，请尽快驶达。\n如对停车场不熟，请点击详情查看停车场的“入口指示”和导航，或联系停车场管理员。');

		$result = $this->noticeMsg($msg_json);


		echo $msg_json;

		echo $result;
	}

    public function pushM(){
        /*发送消息模板给用户的公共号*/
        $msg_json =  sprintf ( C('CUSTOM_TEXT_TPL'), 'oMjtxuH5YZ_6TSkGGLUWvW64aiHQ', "主人，上次使用的抵用券很划算吧！新的抵用券又来了，快来 <a href='http://www.baidu.com'>点击领取>></a>");

        $result = $this->pushMsg($msg_json);


        echo $msg_json;
        echo $result;
    }
}