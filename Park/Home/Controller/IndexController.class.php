<?php

use Think\Controller;

class IndexController extends BaseController {

	private $uid;


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
        include_once(dirname(__FILE__) . '/../Conf/' . 'config_simulation.php');
        
		$cache = $this->getUsercache($this->uid);
		$data = $cache['data'];
		$parkid = $data['parkid'];

		$Order = M('ParkOrder');
		$con = array('pid' => $parkid, 'state' => 0);
		$orderData = $Order->where($con)->order('startime desc')->select();

        $Driver = M('DriverInfo');

		$result = array();
		foreach($orderData as $key => $value){
			$tmp = array();
			$tmp['oid'] = $value['id'];
			$driverId = $value['uid'];
            if(array_key_exists($driverId,$conf_simulation_uids)){
                if($conf_simulation_uids[$driverId]["type"] == 0){
                    $tmp['carid'] = $conf_simulation_uids[$driverId]["id"];
                }else{
                    $tmp['carid'] = $conf_simulation_uids[$driverId]["id_in"];
                }
            }else{
                $tmp['carid'] = $value['carid'];
            }
			$tmp['orderTime'] = $value['startime'];


            $driverData = $Driver->where(array('id' => $driverId))->find();
            $tmp['telephone'] = $driverData['telephone'];

			array_push($result, $tmp);
		}

		$this->ajaxOk($result);
	}



	/*
     *  @desc 车辆进场，设置状态为在场
	 *  @param oid	订单id
    */
	public function setEntry($oid){
        include_once(dirname(__FILE__) . '/../Conf/' . 'config_simulation.php');
        
		$cache = $this->getUsercache($this->uid);
		$data = $cache['data'];
		$parkid = $data['parkid'];
        
        $now = time();
		$Order = M('ParkOrder');
		$con = array('id' => $oid, 'pid' => $parkid);
		$parkorder = $Order->where($con)->find();
		$updateData['state'] = 1;
		$updateData['entrytime'] = date('Y-m-d H:i:s',$now);
		$updateData['updater'] = $this->uid;
        $starttime = strtotime($parkorder['startime']);
        if($now < $starttime){
            //修改停车起始和结束（计费）时间
            $updateData['startime'] = $updateData['entrytime'];
            $endtime = $this->_parkingEndTime($now, $now+100, $parkid);
            $updateData['endtime'] = date('Y-m-d H:i:s',$endtime);

            //推送微信模板信息给用户
            $openid = $this->getOpenID($parkorder['uid']);
            $parkname = $this->getParkName($parkid);
            $adminNick = $this->getAdminNick($this->uid);

            $msg_json =  sprintf ( C('NOTICE_TPL_IN'), $openid, C('TEMPLATE_ID_IN'), C('TEMPLATE_REDIRECT_URL_IN'), '恭喜你的订单已被停车场管理员确认！\n', $parkorder['carid'],$parkname, $updateData['entrytime'],'\n管理员【'.$adminNick.'】已确认你的订单，系统将从现在开始计费!\n如果你尚未到达现场，请在结算时跟管理员说明。');
            $result = $this->noticeMsg($msg_json);
            $result_array = json_decode($result,TRUE);
            if($result_array['errcode'] !=0){
                $this->sendEmail('dubin@duduche.me', "预定模板消息发送失败", "订单号OID：$oid, 错误码：".$result_array['errmsg']);
            }
        }
		$orderData = $Order->where($con)->save($updateData);
        
        $driverId = $parkorder['uid'];
        $carid = $parkorder['carid'];
        $change = 0;                 
                                     
        //自动离场逻辑，同一车牌，在同一停车场，进场后自动把上次未完结的离场
        $map = array();              
        $map['carid'] = $carid;      
        $map['pid'] = $parkid;       
        $map['id'] = array('LT',$oid);
        $map['state'] = array('IN','0,1,2');
        $data = array();             
        $data['state'] = 3;          
        $data['leavetime'] = date('Y-m-d H:i:s');
        $data['driverleave'] = 3;//3表示是后台自动处理的离场
        $data['updater'] = 'Auto';   
        $Order->where($map)->save($data);
                                     
        //添加推广活动积分                   
        if(!array_key_exists($driverId,$conf_simulation_uids) || $conf_simulation_uids[$driverId]["type"] == 1){
                                     
            if(!array_key_exists($driverId,$conf_simulation_uids)){//非测试模式
                                     
                //是否在活动中,来确定增加积分策略   
                $ParkInfo = M('ParkInfo');
                $map = array();      
                $map['id'] = $parkid;
                $parkInfo = $ParkInfo->where($map)->find();
                $acType = $parkInfo['actype'];
                $acScore = $parkInfo['acscore'];
                $acEndtime = strtotime($parkInfo['acendtime']);

                if($acType == 2 && $acEndtime>= $now){//有补助活动，且没有过期
                    if($this->cacheScore($this->uid, $acScore)){//未达到奖励上限
                        $change = $acScore;
                    }
                }
                else{//没有补助或者补助已经过期，采用传统的加分模式
                    $state = C('SCORE');
                    $change = $state['in'];
                }

            }else{
                $change = $conf_simulation_uids[$driverId]["score_in"];
            }
            
        }
        
        if($change > 0){
            $ParkAdmin = M('ParkAdmin');
            $map = array();
            $map['id'] = $this->uid;
            $parkadmin = $ParkAdmin->where($map)->find();
            $oldScore = $parkadmin['score'];
            $this->addScore($this->uid, $change);
            $newScore = $oldScore + $change;
            //记录日志到csv
            $msgs = array();
            $msgs['ip'] = $_SERVER['REMOTE_ADDR'];//用户ip
            $msgs['parkid'] = $parkid;//停车场编号
            $msgs['uid'] = $this->uid;//操作者id
            $msgs['opt'] = 2;//2-代表车辆进场的操作类型
            $msgs['oldValue'] = $oldScore;//原值
            $msgs['newValue'] = $newScore;//新值
            $msgs['change'] = $change;//获得积分
            $msgs['note'] = '';//补充信息
            
            takeCSV($msgs);
        }

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
        include_once(dirname(__FILE__) . '/../Conf/' . 'config_simulation.php');
        
		$cache = $this->getUsercache($this->uid);
		$data = $cache['data'];
		$parkid = $data['parkid'];

		$Order = M('ParkOrder');
		$con = array();
		$con['pid'] = $parkid;
		$con['state'] = array(1,2, 'OR');
		$orderData = $Order->where($con)->order('startime desc')->select();

		$result = array();
		foreach($orderData as $key => $value){
			$tmp = array();
			$tmp['oid'] = $value['id'];
			$driverId = $value['uid'];
            if(array_key_exists($driverId,$conf_simulation_uids)){
                if($conf_simulation_uids[$driverId]["type"] == 0){
                    $tmp['carid'] = $conf_simulation_uids[$driverId]["id"];
                }else{//财神活动不计入在库车辆
                    continue;
                }
                $tmp['telephone'] = $conf_simulation_uids[$driverId]["phone"];
            }else{
                $Driver = M('DriverInfo');
                $con1 = array('id' => $driverId);
                $driverData = $Driver->where($con1)->find();
                $tmp['carid'] = $value['carid'];
                $tmp['telephone'] = $driverData['telephone'];
            }
            
			$tmp['startTime'] = $value['startime'];

			array_push($result, $tmp);
		}

        //对在场列表按照车牌去重
        $list = $this->assoc_unique($result, 'carid');
        $last = array();
        foreach($list as $key => $value){
            array_push($last, $value);
        }

		$this->ajaxOk($last);


	}

	/*
     *  @desc 获取准备离场车辆列表
    */
	public function getLeavings(){    
		include_once(dirname(__FILE__) . '/../Conf/' . 'config_simulation.php');   
		
		$cache = $this->getUsercache($this->uid);
		$data = $cache['data'];
		$parkid = $data['parkid'];

		$Order = M('ParkOrder');
		$con['pid'] = $parkid;
		$con['state'] = array('NEQ',3);
		$con['endtime'] = array('EGT', date('Y-m-d H:i:s'));
		$orderData = $Order->where($con)->select();

		$result = array();
		foreach($orderData as $key => $value){
			$tmp = array();
			$tmp['oid'] = $value['id'];
			$driverId = $value['uid'];
			if(array_key_exists($driverId,$conf_simulation_uids)){   
				if($conf_simulation_uids[$driverId]["type"] == 0){
					$tmp['carid'] = $conf_simulation_uids[$driverId]["id"];
				}else{          
					$tmp['carid'] = $conf_simulation_uids[$driverId]["id_out"];
				}
				$tmp['money'] = 0;
			}else{
			 $tmp['carid'] = $value['carid'];
 
             $Record = M('PaymentRecord');
             $map = array();
             $map['oid'] = $value['id'];
             $map['state'] = 1;
             $pay = $Record->where($map)->sum('money');
             $tmp['money'] = $pay;
            }
            $tmp['startime'] = $value['startime'];
            $tmp['endtime'] = $value['endtime'];
            $tmp['remaintime'] = strtotime($value['endtime']) - time();
            $tmp['stoptime'] = time() - strtotime($value['startime']);

			array_push($result, $tmp);
		}

		$this->ajaxOk($result);


	}

	/*
     *  @desc 车辆离场
	 *  @param oid	订单id
    */
	public function setLeave($oid){
        include_once(dirname(__FILE__) . '/../Conf/' . 'config_simulation.php');
        
		$cache = $this->getUsercache($this->uid);
		$data = $cache['data'];
		$parkid = $data['parkid'];
        
        $now = time();
		$Order = M('ParkOrder');
		$con = array('id' => $oid, 'pid' => $parkid);
		$updateData['state'] = 3;
		$updateData['leavetime'] = date('Y-m-d H:i:s');
		$updateData['updater'] = $this->uid;
		$orderData = $Order->where($con)->save($updateData);
        $driverId = $Order->where($con)->getField('uid');
        $change = 0;
        
        if(!array_key_exists($driverId,$conf_simulation_uids) || $conf_simulation_uids[$driverId]["type"] == 1){
            
            if(!array_key_exists($driverId,$conf_simulation_uids)){//非测试模式
                //是否在活动中,来确定增加积分策略
                $ParkInfo = M('ParkInfo');
                $map = array();
                $map['id'] = $parkid;
                $parkInfo = $ParkInfo->where($map)->find();
                $acType = $parkInfo['actype'];
                $acScore = $parkInfo['acscore'];
                $acEndtime = strtotime($parkInfo['acendtime']);
                
                if($acType == 1 && $acEndtime>= $now){//有补助活动，且没有过期
                    if($this->cacheScore($this->uid, $acScore)){//未达到奖励上限
                        $change = $acScore;
                    }
                }
                else{//没有补助或者补助已经过期，采用传统的加分模式
                    $state = C('SCORE');
                    $change = $state['out'];
                }
            }else{
                $change = $conf_simulation_uids[$driverId]["score_out"];
            }
            
        }
        
        if($change > 0){
            $ParkAdmin = M('ParkAdmin');
            $map = array();
            $map['id'] = $this->uid;
            $parkadmin = $ParkAdmin->where($map)->find();
            $oldScore = $parkadmin['score'];
            $this->addScore($this->uid, $change);
            $newScore = $oldScore + $change;
            //记录日志到csv
            $msgs = array();
            $msgs['ip'] = $_SERVER['REMOTE_ADDR'];//用户ip
            $msgs['parkid'] = $parkid;//停车场编号
            $msgs['uid'] = $this->uid;//操作者id
            $msgs['opt'] = 3;//3-代表车辆离场的操作类型
            $msgs['oldValue'] = $oldScore;//原值
            $msgs['newValue'] = $newScore;//新值
            $msgs['change'] = $change;//获得积分
            $msgs['note'] = '';//补充信息
            
            takeCSV($msgs);
        }


        if($orderData !== false){
			$this->ajaxOk("");
		}
		else{
			$this->ajaxMsg("车辆离场失败！");
		}


	}

	/*
     *  @desc 获取交易信息
	 *  @param $lastWeek	0-全部，1-最近一周交易
	 *  @param $all 	0-已经离场的交易，1-所有下单成功了的订单
    */
	public function getDeals($lastweek, $all=0){
        include_once(dirname(__FILE__) . '/../Conf/' . 'config_biz.php');
		$cache = $this->getUsercache($this->uid);
		$data = $cache['data'];
		$parkid = $data['parkid'];

		$Order = M('ParkOrder');
		$beroreWeek = date("Y-m-d",strtotime("-1 week"));
		$map = array();
		$map['pid'] = $parkid;
        if($all == 1){
            $map['state'] = array('EGT', 0);
            if($lastweek == 1){
                $map['startime'] = array('EGT', $beroreWeek);
            }
        }
		else{
            $map['state'] = 3;
            if($lastweek == 1){
                $map['leavetime'] = array('EGT', $beroreWeek);
            }
        }

		$orderData = $Order->where($map)->order('startime desc')->select();

		$result = array();
		foreach($orderData as $key => $value){
			$tmp = array();
            $tmp['oid'] = $value['id'];
            $tmp['s'] = $value['state'];
            if($tmp['s'] < 1){//未入库
                $tmp['startime'] = date('Y-m-d H:i:s', strtotime($value['startime'])-$config_order_wait_sesc);
                $tmp['admin'] = null;
            }else{
                $tmp['startime'] = $value['startime'];
                $tmp['admin'] = $this->getAdmin($value['updater']);
            }
            $tmp['endtime'] = $value['endtime'];

			$Payment = M('PaymentRecord');
			$map = array('oid' => $value['id'], 'state'=>1);
			$payData = $Payment->where($map)->select();
			$sum = 0;
			foreach($payData as $key1 => $value1){
				$sum = $sum + $value1['money'];
			}
			$tmp['money'] = $sum;
			$tmp['carid'] = $value['carid'];
            if($tmp['carid'] == '' && $value['uid'] == 0){
                $tmp['carid'] = '测A88888';
            }
            $tmp['tel'] = $this->getDriver($value['uid'])['telephone'];

			array_push($result, $tmp);

		}


		$this->ajaxOk($result);

	}

	/*
     *  @desc 设置停车场空位情况
	 *  @param $state 车位情况 0-已满 1-较少 2-较多
    */
	public function setParkState($state){
		$cache = $this->getUsercache($this->uid);
		$data = $cache['data'];
		$parkid = $data['parkid'];

        $ParkAdmin = M('ParkAdmin');
        $map = array();
        $map['id'] = $this->uid;
        $parkadmin = $ParkAdmin->where($map)->find();
        $oldScore = $parkadmin['score'];

		$Park = M('ParkInfo');
		$data = array();
		$data['id'] = $parkid;
		$data['parkstate'] = $state;
        $data['laststateop'] =  date('Y-m-d H:i:s');
		$data['updater'] = $this->uid;
		$result = $Park->save($data);

        //增加操作积分
		$score = C('SCORE');
		$this->addScore($this->uid, $score['state']);

        $newScore = $oldScore + $score['state'];

        //记录日志到csv
        $msgs = array();
        $msgs['ip'] = $_SERVER['REMOTE_ADDR'];//用户ip
        $msgs['parkid'] = $parkid;//停车场编号
        $msgs['uid'] = $this->uid;//操作者id
        $msgs['opt'] = 1;//1-代表空车位变更的操作类型
        $msgs['oldValue'] = $oldScore;//原值
        $msgs['newValue'] = $newScore;//新值
        $msgs['change'] = $score['state'];//获得积分
        $msgs['note'] = '';//补充信息

        takeCSV($msgs);

		if(empty($result)){
			$this->ajaxMsg("修改状态失败！");
		}
		else{
			$this->ajaxOk(null);
		}

	}


	/*
     *  @desc 设置pushid
	 *  @param $pushid
    */
	public function setPushId($pushid){

		$ParkAdmin = M('ParkAdmin');
		$data = array();
		$data['id'] = $this->uid;
		$data['pushid'] = $pushid;

		$result = $ParkAdmin->save($data);

		if($result === false){
			$this->ajaxMsg("更新pushid失败！id：".$this->uid);
		}
		else{
			$this->ajaxOk(null);
		}

	}

	/*
     *  @desc 获取管理员端基本信息
    */
	public function getBaseInfo()
	{

		$cache = $this->getUsercache($this->uid);
		$data = $cache['data'];
		$parkid = $data['parkid'];

		$Park = M('ParkInfo');
		$con = array();
		$con[id] = $parkid;
		$parkData =  $Park->where($con)->find();

		if(empty($parkData)){
			$this->ajaxMsg("用户名错误:".$this->uid);
		}
		else{
			$result['parkstate'] = $parkData['parkstate'];
            //停车场推广活动
            $result['actype'] = $parkData['actype'];
            $result['acscore'] = $parkData['acscore'];
            $result['acendtime'] = $parkData['acendtime'];
            $balance = $parkData['balance'] > $parkData['upfront']?$parkData['balance'] - $parkData['upfront']:0;
		}


        //预付完，还未进场的
		$Order = M('ParkOrder');
		$map = array();
		$map['pid'] = $parkid;
		$map['state'] = 0;
        $beroreWeek = date("Y-m-d",strtotime("-1 week"));
        $map['startime'] = array('EGT', $beroreWeek);//一个星期以内的
		$orderData = $Order->where($map)->select();
		$result['in'] = count($orderData);

        include_once(dirname(__FILE__) . '/../Conf/' . 'config_simulation.php');
		$map = array();
        $uid_excludes = array();
		foreach ($conf_simulation_uids as $key => $value) {//财神不计入在库
            if($value['type']==1){
                $uid_excludes[] = $key;
            }
        }
        if(count($uid_excludes) > 0){
            $map['uid'] = array('not in',$uid_excludes);
        }
		$map['pid'] = $parkid;
		$map['state'] = array(1,2, 'OR');
		$orderData = $Order->where($map)->select();
        $carids = array();
        foreach($orderData as $key => $value){//去除在场重复的车辆
            $driverId = $value['uid'];
            $carid = $value['carid'];
            if(in_array($carid, $carids))
            {
                continue;
            }
            else {
                $carids[] = $carid;
            }
        }
		$result['at'] = count($carids);



		$map = array();
		$map['pid'] = $parkid;
		$map['state'] = array('NEQ',3);
		$map['endtime'] = array('EGT', date('Y-m-d H:i:s'));
		$orderData = $Order->where($map)->select();
		$result['out'] = count($orderData);



		$beroreWeek = date("Y-m-d",strtotime("-1 week"));
		$map = array();
		$map['pid'] = $parkid;
		$map['state'] = 3;
		$map['leavetime'] = array('EGT', $beroreWeek);
		$orderData = $Order->where($map)->select();
		$result['deals'] = count($orderData);

		$result['name'] = $this->getAdmin($this->uid);

		//积分
		$ParkAdmin = M('ParkAdmin');
		$map = array();
		$map['id'] = $this->uid;
		$admin = $ParkAdmin->where($map)->find();
		$result['score'] = $admin['score'];

		//今日收益
		$map = array();
		$map['pid'] = $parkid;
		$orderDatas = $Order->where($map)->select();
		$Payment = M('PaymentRecord');
		$today = 0;
		foreach ($orderDatas as $key => $value) {
			$map = 'oid = '.$value['id'].' AND state = 1 AND TO_DAYS(updatetime) = TO_DAYS(NOW())';
			$today += $Payment->where($map)->sum('money');
		}
		$result['todaysum'] = $today;
		//可以提现

		$DrawMoney = M('DrawMoney');
		$map = array();
		$map['pid'] = $parkid;
        $map['state'] = 0;
		$drawSum = $DrawMoney->where($map)->sum('money');
		$remainMoney = $balance - $drawSum;//余额-未兑现的提现
		$result['remainsum'] = $remainMoney;


        //总订单量
        $map = array();
        $map['pid'] = $parkid;
        $map['state'] = array('EGT', 0);
        $tn = $Order->where($map)->count();
        $result['tn'] = $tn;

        //今日订单量
        $map = "pid = $parkid and state > -1 and TO_DAYS(createtime) = TO_DAYS(NOW())";
        $n = $Order->where($map)->count();
        $result['n'] = $n;



		$this->ajaxOk($result);

	}


	/*
     *  @desc 获取提现的基本信息
    */
	public function getMoneyBase()
	{
		$cache = $this->getUsercache($this->uid);
		$data = $cache['data'];
		$parkid = $data['parkid'];

		$result = array();

		$Order = M('ParkOrder');


		//计算总交易次数
		$map = array();
		$map['pid'] = $parkid;
		$map['state'] = 3;
		$dealNum = $Order->where($map)->count();
		$result['dealNum'] = $dealNum;

		//计算总收益
		$map = array();
		$map['pid'] = $parkid;
		$orderData = $Order->where($map)->select();

		$Payment = M('PaymentRecord');
		$sum = 0;
		foreach ($orderData as $key => $value) {
			$map = array();
			$map['oid'] = $value['id'];
			$map['state'] = 1;
			$sum += $Payment->where($map)->sum('money');
		}
		$result['sum'] = $sum;

		//计算今日收益
		$today = 0;
		foreach ($orderData as $key => $value) {
			$map = 'oid = '.$value['id'].' AND state = 1 AND TO_DAYS(updatetime) = TO_DAYS(NOW())';
			$today += $Payment->where($map)->sum('money');
		}
		$result['todaysum'] = $today;

		//计算可提现金额
        $Park = M('ParkInfo');
        $con = array();
        $con[id] = $parkid;
        $parkData =  $Park->where($con)->find();
        $balance = $parkData['balance'] > $parkData['upfront']?$parkData['balance'] - $parkData['upfront']:0;
        $upfront = $parkData['upfront'];

		$DrawMoney = M('DrawMoney');
		$map = array();
		$map['pid'] = $parkid;
        $map['state'] = 0;
		$drawSum = $DrawMoney->where($map)->sum('money');

		$remainMoney = $balance - $drawSum;//余额-未兑现的提现
		$result['remainSum'] = $remainMoney;
        $result['upfront'] = $upfront;


		//提现记录
		$map = array();
		$map['pid'] = $parkid;
		$drawLogs = $DrawMoney->where($map)->limit(10)->order('createtime desc')->select();


		$drawLists = array();
		foreach($drawLogs as $key => $value){
			$tmp = array();
			$tmp['accountname'] = $value['accountname'];
			$tmp['bankname'] = $value['bankname'];
			$tmp['account'] = $value['account'];
			$tmp['money'] = $value['money'];
            $tmp['visitype'] = $value['visitype'];
            $tmp['name'] = $value['name'];
            $tmp['telephone'] = $value['telephone'];
 			$tmp['opttime'] = $value['createtime'];
			$tmp['optname'] = $this->getAdmin($value['creater']);
			$tmp['state'] = $value['state'];

			array_push($drawLists, $tmp);
		}


        //提现信息缓存
        $map = array();
        $map['creater'] = $this->uid;
        $map['visitype'] = 0;
        $draw0 = $DrawMoney->where($map)->order('createtime desc')->find();
        if(!empty($draw0)){
            $t0 = array('bankname' => $draw0['bankname'], 'accountname' => $draw0['accountname'], 'account' => $draw0['account'],
                        'name' => $draw0['name'], 'telephone' => $draw0['telephone']);
        }
        else{
            $t0 = array('bankname' => '', 'accountname' => '', 'account' => '',
                'name' => '', 'telephone' => '');
        }

        $map = array();
        $map['creater'] = $this->uid;
        $map['visitype'] = 1;
        $draw1 = $DrawMoney->where($map)->order('createtime desc')->find();
        if(!empty($draw1)){
            $t1 = array('name' => $draw1['name'], 'telephone' => $draw1['telephone']);
        }
        else{
            $t1 = array('name' => '', 'telephone' => '');
        }

        $result['drawCache'] = array( 0 => $t0, 1 => $t1);

		$result['drawLists'] = $drawLists;
		$this->ajaxOk($result);

	}


	/*
     *  @desc 提现的请求处理
    */
	public function drawMoney(){
		$bankname = I('get.bankname');
		$accountname = I('get.accountname');
		$account = I('get.account');
		$money = I('get.money');
		$name = I('get.name');
		$telephone = I('get.telephone');
        $visitype = I('get.visitype');

		$cache = $this->getUsercache($this->uid);
		$data = $cache['data'];
		$parkid = $data['parkid'];

		//判断是否超过可提现额度
        $Park = M('ParkInfo');
        $con = array();
        $con[id] = $parkid;
        $parkData =  $Park->where($con)->find();
        $balance = $parkData['balance'];

        $DrawMoney = M('DrawMoney');
        $map = array();
        $map['pid'] = $parkid;
        $map['state'] = 0;
        $drawSum = $DrawMoney->where($map)->sum('money');

        $remainMoney = $balance - $drawSum;//余额-未兑现的提现

		if($money > $remainMoney){
			$this->ajaxMsg('超过最大可提现金额！');
		}


		$data =array('bankname' => $bankname, 'accountname' => $accountname, 'account' => $account, 'money' => $money,
			'name' => $name, 'telephone' => $telephone, 'pid' => $parkid, 'visitype' =>$visitype, 'state' => 0, 'creater' => $this->uid,
			'createtime' =>  date('Y-m-d H:i:s'), 'updater' =>$this->uid);

		$drawId = $DrawMoney->add($data);

		$title = '[提现请求]';
		$parkName = $this->getParkName($parkid);

        if($visitype == C('VISIT_TYPE')['Online']){
            $visitypeStr = "线上银行卡转账";
            $content = '停车场：'.$parkName.'<br>提现方式：'.$visitypeStr.'<br>账户名：'.$accountname.'<br>开户银行：'.$bankname.'<br>账号：'.$account.
                '<br>姓名：'.$name.'<br>联系电话：'.$telephone.'<br>提现金额：'.$money.'<br>提现表ID：'.$drawId;
        }
        else{
            $visitypeStr = "业务人员线下送上门";
            $content = '停车场：'.$parkName.'<br>提现方式：'.$visitypeStr. '<br>姓名：'.$name.'<br>联系电话：'.$telephone.'<br>提现金额：'.$money
                .'<br>提现表ID：'.$drawId;
        }




		if(empty($drawId)){
			$this->ajaxMsg('提现请求失败！');
		}
		else{
            $map = array();
            $map['id'] = $parkid;
            $ParkInfo = M('ParkInfo');
            $status = $ParkInfo->where($map)->getField('status');
            if($status%10 == 4 ){//已经合作的才会发送提现邮件
                $send = $this->sendEmail('all@duduche.me', $title, $content);
            }
			$this->ajaxOk('');
		}

	}
	/*
         *  @desc 获取礼品列表的基本信息
        */
	public function getGiftBase(){

		$result = array();
		$ParkAdmin = M('ParkAdmin');
		$map = array();
		$map['id'] = $this->uid;
		$admin = $ParkAdmin->where($map)->find();
		$result['score'] = $admin['score'];

		$GiftList = M('GiftList');
		$map = array();
		$map['valid'] = 1;
		$giftData = $GiftList->where($map)->order('weight')->select();

		$giftList = array();

		foreach($giftData as $key => $value){
			$tmp = array();
			$tmp['gid'] = $value['id'];
			$tmp['name'] = $value['name'];
			$tmp['score'] = $value['score'];
            $tmp['type'] = $value['type'];
			$tmp['image'] = $value['image'];

			array_push($giftList, $tmp);
		}

		$result['giftList'] = $giftList;


        //兑换信息缓存
        $Model = new \Think\Model(); // 实例化一个model对象 没有对应任何数据表
        $sql0 = 'select e.name, e.address, e.telephone from dudu_exchange_gift e, dudu_gift_list g where e.gid = g.id and g.type = 0 and e.visitype = 0 and e.creater='.$this->uid.' Order By e.createtime desc';
        $list0 = $Model->query($sql0);
        if(!empty($list0)){
            $t0 = array('name'=> $list0[0]['name'], 'address'=> $list0[0]['address'], 'telephone' => $list0[0]['telephone']);
        }
        else{
            $t0 = array('name'=> '', 'address'=> '', 'telephone' => '');
        }

        $sql1 = 'select e.name, e.bankname, e.account from dudu_exchange_gift e, dudu_gift_list g where e.gid = g.id and g.type = 1 and e.visitype = 0 and e.creater='.$this->uid.' Order By e.createtime desc';
        $list1 = $Model->query($sql1);
        if(!empty($list1)){
            $t1 = array('name'=> $list1[0]['name'], 'bankname'=> $list1[0]['bankname'], 'account' => $list1[0]['account']);
        }
        else{
            $t1 = array('name'=> '', 'bankname'=> '', 'account' => '');
        }

        $result['exCache'] = array(0 => $t0, 1 => $t1);


		$this->ajaxOk($result);
	}

    /*
     *  @desc 获取兑换记录列表
    */
    public function getExList(){
        $ExchangeGift = M('ExchangeGift');
        $map = array();
        $map['creater'] = $this->uid;
        $data = $ExchangeGift->where($map)->order('state, createtime desc')->select();

        $result = array();

        foreach($data as $key => $value){
            $temp = array();
            if($value['visitype'] == 1){
                $temp['visitype'] = 1;
            }
            else{
                if($this->getGift($value['gid'])['type']==0){
                    $temp['visitype'] = 2;
                    $temp['name'] = $value['name'];
                    $temp['address'] = $value['address'];
                    $temp['telephone'] = $value['telephone'];
                }
                else{
                    $temp['visitype'] = 3;
                    $temp['name'] = $value['name'];
                    $temp['account'] = $value['account'];
                    $temp['bankname'] = $value['bankname'];
                }
            }

            $temp['createtime'] = $value['createtime'];
            $temp['score'] = $value['score'];
            $temp['state'] = $value['state'];
            $temp['giftname'] = $this->getGiftName($value['gid']);

            array_push($result, $temp);

        }

        $this->ajaxOk($result);
    }

	/*
     *  @desc 积分兑换礼品的请求处理
    */
	public function exchangeGift()
	{
		$name = I('get.name');
		$address = I('get.address');
		$telephone = I('get.telephone');
        $bankname = I('get.bankname');
        $account = I('get.account');
        $visitype = I('get.visitype');
		$gid = I('get.gid');

		$GifgList = M('GiftList');
		$map = array();
		$map['id'] = $gid;
        $map['valid'] = 1;
		$giftData = $GifgList->where($map)->find();
		if(empty($giftData)){
			$this->ajaxMsg('该礼物已兑换完！');
		}
		else{
			$score = $giftData['score'];
            $type = $giftData['type'];
		}

		$cache = $this->getUsercache($this->uid);
		$data = $cache['data'];
		$parkid = $data['parkid'];

		$ParkAdmin = M('ParkAdmin');
		$map = array();
		$map['id'] = $this->uid;
		$admin = $ParkAdmin->where($map)->find();

		$scoreSum = $admin['score'];

		if($score > $scoreSum){
			$this->ajaxMsg('积分不够！');
		}

		$ExchangeGift = M('ExchangeGift');
		$data =array('name' => $name, 'address' => $address, 'telephone' => $telephone, 'bankname' => $bankname, 'account' => $account,
            'visitype' => $visitype, 'score' => $score,
			'pid' => $parkid, 'gid' => $gid, 'state' => 0, 'creater' => $this->uid, 'createtime' =>  date('Y-m-d H:i:s'), 'updater' =>$this->uid);

		$exid = $ExchangeGift->add($data);

        //Email数据准备
		$title = '[兑换礼品请求]';
		$parkName = $this->getParkName($parkid);
		$giftName = $this->getGiftName($gid);
		$adminName = $this->getAdmin($this->uid);
        if($visitype == C('VISIT_TYPE')['Online']){
            if($type == 0 ){
                $visitypeStr = "快递寄送礼品";
                $content = '停车场：'.$parkName.'<br>姓名：'.$name.'<br>地址：'.$address.'<br>电话：'.$telephone.
                    '<br>送货方式：'.$visitypeStr.
                    '<br>礼品名称：'.$giftName.'<br>兑换管理员：'.$adminName.'<br>兑换积分：'.$score.'<br>兑换表ID：'.$exid;
            }
            else{
                $visitypeStr = "银行卡转账";
                $content = '停车场：'.$parkName.'<br>姓名：'.$name.
                    '<br>开户行：'.$bankname.'<br>银行账号：'.$account.'<br>送货方式：'.$visitypeStr.
                    '<br>礼品名称：'.$giftName.'<br>兑换管理员：'.$adminName.'<br>兑换积分：'.$score.'<br>兑换表ID：'.$exid;
            }

        }
        else{
            $visitypeStr = "销售线下送上门";
            $content = '停车场：'.$parkName.'<br>送货方式：'.$visitypeStr.
                '<br>礼品名称：'.$giftName.'<br>兑换管理员：'.$adminName.'<br>兑换积分：'.$score.'<br>兑换表ID：'.$exid;

        }



		//更新积分
		$scoreSum = $scoreSum - $score;
		$map = array();
		$map['id'] = $this->uid;
		$savedata['score'] = $scoreSum;
		$ParkAdmin->where($map)->save($savedata);

		if(empty($exid)){
			$this->ajaxMsg('兑换请求失败！');
		}
		else{
            $map = array();
            $map['id'] = $parkid;
            $ParkInfo = M('ParkInfo');
            $status = $ParkInfo->where($map)->getField('status');
            if($status%10 == 4 ){//已经合作的才会发送提现邮件
                $send = $this->sendEmail('all@duduche.me', $title, $content);
            }

            //记录日志到csv
            $newScore = $scoreSum;
            $change = $score;
            $oldScore = $newScore - $change;
            $msgs = array();
            $msgs['ip'] = $_SERVER['REMOTE_ADDR'];//用户ip
            $msgs['parkid'] = $parkid;//停车场编号
            $msgs['uid'] = $this->uid;//操作者id
            $msgs['opt'] = 4;//4-代表兑换积分
            $msgs['oldValue'] = $oldScore;//原值
            $msgs['newValue'] = $newScore;//新值
            $msgs['change'] = $change;//获得积分
            $msgs['note'] = $gid;//补充信息,兑换礼品表的id

            takeCSV($msgs);


			$this->ajaxOk('');
		}


	}


    /*
    *  @desc 计算停车场费用
     * $oid 订单号
   */
    public function calDeal($oid){

        $ParkOrder = M('ParkOrder');
        $map = array();
        $map['id'] = $oid;
        $orderData = $ParkOrder->where($map)->find();

        $p = $this->parkingFee(strtotime($orderData['startime']), $orderData['pid']);
        $result['p'] = $p;

        $this->ajaxOk($result);

    }
}