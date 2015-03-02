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
		$orderData = $Order->where($con)->order('startime desc')->select();

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
		$updateData['entrytime'] = date('Y-m-d H:i:s');
		$updateData['updater'] = $this->uid;
		$orderData = $Order->where($con)->save($updateData);

		$state = C('SCORE');
		$this->addScore($this->uid, $state['in']);

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
		$con = array();
		$con['pid'] = $parkid;
		$con['state'] = array(1,2, 'OR');
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
			$tmp['telephone'] = $driverData['telephone'];
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
		$con['pid'] = $parkid;
		$con['state'] = array('NEQ',3);
		$con['endtime'] = array('EGT', date('Y-m-d H:i:s'));
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
			$tmp['startime'] = $value['startime'];
			$tmp['endtime'] = $value['endtime'];
			$tmp['remaintime'] = strtotime($value['endtime']) - time();

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
		$updateData['leavetime'] = date('Y-m-d H:i:s');
		$updateData['updater'] = $this->uid;
		$orderData = $Order->where($con)->save($updateData);

		$state = C('SCORE');
		$this->addScore($this->uid, $state['out']);

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
    */
	public function getDeals($lastweek){

		$cache = $this->getUsercache($this->uid);
		$data = $cache['data'];
		$parkid = $data['parkid'];

		$Order = M('ParkOrder');
		$beroreWeek = date("Y-m-d",strtotime("-1 week"));
		$map = array();

		$map['pid'] = $parkid;
		$map['state'] = 3;
		if($lastweek == 1){
			$map['leavetime'] = array('EGT', $beroreWeek);
		}
		$orderData = $Order->where($map)->order('leavetime desc')->select();

		$result = array();
		foreach($orderData as $key => $value){
			$tmp = array();
			$tmp['startime'] = $value['startime'];
			$tmp['endtime'] = $value['endtime'];

			$Payment = M('PaymentRecord');
			$map = array('oid' => $value['id'], 'state'=>1);
			$payData = $Payment->where($map)->select();
			$sum = 0;
			foreach($payData as $key1 => $value1){
				$sum = $sum + $value1['money'];
			}
			$tmp['money'] = $sum;

			$Driver = $this->getDriver($value['uid']);
			if(!empty($Driver)) {
				$tmp['carid'] = $Driver['carid'];
			}

			$tmp['admin'] = $this->getAdmin($value['updater']);
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

		$Park = M('ParkInfo');
		$data = array();
		$data['id'] = $parkid;
		$data['parkstate'] = $state;
		$data['updater'] = $this->uid;
		$result = $Park->save($data);

		$score = C('SCORE');
		$this->addScore($this->uid, $score['state']);

		$states = array('已满','较少','较多');
		$logStr = '停车场：'.$this->getParkName($parkid).' 管理员：'.$this->getAdmin($this->uid).' 设置状态：'.$states[$state]
			.' 积分：'.$score['state'].' 时间：'.date('Y-m-d H:i:s');
		Think\Log::write($logStr,'setparkstate.log');

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
		}


		$Order = M('ParkOrder');
		$map = array();
		$map['pid'] = $parkid;
		$map['state'] = 0;
		$orderData = $Order->where($map)->select();
		$result['in'] = count($orderData);


		$map = array();
		$map['pid'] = $parkid;
		$map['state'] = array(1,2, 'OR');
		$orderData = $Order->where($map)->select();
		$result['at'] = count($orderData);


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
		$sum = 0;
		foreach ($orderDatas as $key => $value) {
			$map = array();
			$map['oid'] = $value['id'];
			$map['state'] = 1;
			$sum += $Payment->where($map)->sum('money');
		}

		$DrawMoney = M('DrawMoney');
		$map = array();
		$map['pid'] = $parkid;
		$drawSum = $DrawMoney->where($map)->sum('money');
		$remainMoney = $sum - $drawSum;
		$result['remainsum'] = $remainMoney;



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
		$DrawMoney = M('DrawMoney');
		$map = array();
		$map['pid'] = $parkid;
		$drawSum = $DrawMoney->where($map)->sum('money');
		$remainMoney = $sum - $drawSum;
		$result['remainSum'] = $remainMoney;


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
 			$tmp['opttime'] = $value['createtime'];
			$tmp['optname'] = $this->getAdmin($value['creater']);
			$tmp['state'] = $value['state'];

			array_push($drawLists, $tmp);
		}

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

		$cache = $this->getUsercache($this->uid);
		$data = $cache['data'];
		$parkid = $data['parkid'];

		$DrawMoney = M('DrawMoney');

		//判断是否超过可提现额度
		//计算总收益
		$Order = M('ParkOrder');
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

		//计算可提现金额

		$map = array();
		$map['pid'] = $parkid;
		$drawSum = $DrawMoney->where($map)->sum('money');
		$remainMoney = $sum - $drawSum;

		if($money > $remainMoney){
			$this->ajaxMsg('超过最大可提现金额！');
		}


		$data =array('bankname' => $bankname, 'accountname' => $accountname, 'account' => $account, 'money' => $money,
			'name' => $name, 'telephone' => $telephone, 'pid' => $parkid, 'state' => 0, 'creater' => $this->uid,
			'createtime' =>  date('Y-m-d H:i:s'), 'updater' =>$this->uid);

		$drawId = $DrawMoney->add($data);

		$title = '[提现请求]';
		$parkName = $this->getParkName($parkid);
		$content = '停车场：'.$parkName.'<br>账户名：'.$accountname.'<br>开户银行：'.$bankname.'<br>账号：'.$account.
			'<br>姓名：'.$name.'<br>提现金额：'.$money.'<br>提现表ID：'.$drawId;

		if(empty($drawId)){
			$this->ajaxMsg('提现请求失败！');
		}
		else{
			$send = $this->sendEmail('295142831@qq.com', $title, $content);
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
			$tmp['image'] = $value['image'];

			array_push($giftList, $tmp);
		}

		$result['giftList'] = $giftList;

		$this->ajaxOk($result);
	}


	/*
     *  @desc 提现的请求处理
    */
	public function exchangeGift()
	{
		$name = I('get.name');
		$address = I('get.address');
		$telephone = I('get.telephone');
		$gid = I('get.gid');

		$GifgList = M('GiftList');
		$map = array();
		$map['id'] = $gid;
		$giftData = $GifgList->where($map)->find();
		if(empty($giftData)){
			$this->ajaxMsg('该礼物已兑换完！');
		}
		else{
			$score = $giftData['score'];
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
		$data =array('name' => $name, 'address' => $address, 'telephone' => $telephone, 'score' => $score,
			'pid' => $parkid, 'gid' => $gid, 'state' => 0, 'creater' => $this->uid, 'createtime' =>  date('Y-m-d H:i:s'), 'updater' =>$this->uid);

		$exid = $ExchangeGift->add($data);

		$title = '[兑换礼品请求]';
		$parkName = $this->getParkName($parkid);
		$giftName = $this->getGiftName($gid);
		$adminName = $this->getAdmin($this->uid);
		$content = '停车场：'.$parkName.'<br>姓名：'.$name.'<br>地址：'.$address.'<br>电话：'.$telephone.
			'<br>礼品名称：'.$giftName.'<br>兑换管理员：'.$adminName.'<br>兑换积分：'.$score.'<br>兑换表ID：'.$exid;

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
			$send = $this->sendEmail('295142831@qq.com', $title, $content);
			$this->ajaxOk('');
		}


	}
}