<?php
    
define('XHPROF_ENABLE',0);

//推送
define('GT_APPKEY','SmksDDicdNA2GtpF4l7Sc5');
define('GT_APPID','dpEB6vgxrFABEctm95ZsB3');
define('GT_MASTERSECRET','wTd4AqonHlArztm0xiaYJ4');
define('GT_HOST','http://sdk.open.api.igexin.com/apiex.htm');

/**
 * 后台基础控制器
 * @Bin
 */
class BaseController extends \Think\Controller {
    
    public function _initialize(){
        $this->_start_prof();
    }
    
    protected function _start_prof(){
        if(XHPROF_ENABLE){
            $XHPROF_ROOT = realpath(dirname(__FILE__) .'/../Common');
            include_once $XHPROF_ROOT . "/xhprof_lib/utils/xhprof_lib.php";
            include_once $XHPROF_ROOT . "/xhprof_lib/utils/xhprof_runs.php";
            
            // start profiling
            xhprof_enable(XHPROF_FLAGS_CPU+XHPROF_FLAGS_NO_BUILTINS);
        }
    }
    
    protected function _end_prof(){
        if(XHPROF_ENABLE){
            // stop profiler
            $xhprof_data = xhprof_disable();
            
            // save raw data for this profiler run using default
            // implementation of iXHProfRuns.
            $xhprof_runs = new XHProfRuns_Default();
            
            // save the run under a namespace "xhprof_foo"
            $run_id = $xhprof_runs->save_run($xhprof_data, "xhprof_driver");
        }
    }
    
    protected function _exit(){
        $this->_end_prof();
        
        exit;
    }

    protected function createUUID($uid){
        mt_srand((double)microtime()*10000);//optional for php 4.2.0 and up.
        $charid = strtoupper(md5(uniqid(rand(), true)));
        $hyphen = chr(45);// "-"
        $uuid =  substr($charid, 0, 8).$hyphen
                .substr($charid, 8, 4).$hyphen
                .substr($charid,12, 4).$hyphen
                .substr($charid,16, 4).$hyphen
                .substr($charid,20,12);

        $cachekey = $this->getCacheKey($uuid);



        S($cachekey,array(
            'uid' => $uid,
            'uuid' =>$uuid,
        ),C('DATA_CACHE_TIME'));

        return $uuid;
    }

    protected function getCacheKey($uuid){
        return '____usercachekey___'.$uuid;
    }

    protected function getUsercache($uuid){
        $key = $this->getCacheKey($uuid);
        $data = S($key);
        return $data;
    }

    protected function sendmsg($code, $data, $ext = null){
        $this->_end_prof();
        
        $result = array(
                    'code'=>$code,
                    'data'=>$data
                );
        if($ext){
        	$this->ajaxReturn(array_merge($result,$ext),'jsonp');
        }else{
					$this->ajaxReturn($result,'jsonp');
				}
    }

    
    protected function ajaxOk($data,$ext=null){
        $this->sendmsg(0,$data,$ext);
        exit;
    }
    protected function ajaxMsg($msg,$ext=null){
        $this->sendmsg(10,$msg,$ext);
        exit;
    }
    protected function ajaxFail($ext=null){
        $this->sendmsg(100,"",$ext);
        exit;
    }

    //获取随机字符串
    protected function getRandChar($length){
        $str = null;
        $strPol = "ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789abcdefghijklmnopqrstuvwxyz";
        $max = strlen($strPol)-1;

        for($i=0;$i<$length;$i++){
            $str.=$strPol[rand(0,$max)];//rand($min,$max)生成介于min和max两个数之间的一个随机整数
        }

        return $str;
    }


    /**
     *  @desc 根据UID获得openid
     *  @param int $uid
     */
    protected function getOpenID($uid){
        $DriverInfo = M('DriverInfo');
        $map = array();
        $map['id'] = $uid;
        $driverData = $DriverInfo->where($map)->find();
        if(empty($driverData)){
            return null;
        }
        else{
            return $driverData['openid'];
        }
    }


    /**
     *  @desc 根据UID获得默认车牌号
     *  @param int $uid
     */
    protected function getDefualtCarid($uid){
        $DriverCar = M('DriverCar');
        $map = array();
        $map['driverid'] = $uid;
        $map['status'] = 1;
        $car = $DriverCar->where($map)->find();
        if(empty($car)){
            return null;
        }
        else{
            return $car['carid'];
        }
    }

    /**
     *  @desc 根据uid 去车主的车牌
     *  @param $uid
     */
    protected function getDriver($uid)
    {
        $Driver = M('DriverInfo');
        $map = array();
        $map['id'] = $uid;
        $driverData = $Driver->where($map)->find();
        if(!empty($driverData)){
            return $driverData;
        }
        else{
            return null;
        }

    }

    /**
     *  @desc $pid 获取停车场名字
     *  @param $pid
     */
    protected function getParkName($pid)
    {
        $ParkInfo = M('ParkInfo');
        $map = array();
        $map['id'] = $pid;
        $parkData = $ParkInfo->where($map)->find();
        if(!empty($parkData)) {
            return  $parkData['name'];
        }
        else{
            return null;
        }

    }


    /**
     *  @desc  发送邮件
     *  @param $adid
     */
    protected function sendEmail($mail, $title, $content)
    {
        return SendMail($mail, $title, $content);
    }

    /**
     *  @desc 根据两点间的经纬度计算距离
     *  @param float $lat 纬度值
     *  @param float $lng 经度值
     */
    protected function getDistance($lat1, $lng1, $lat2, $lng2)
    {
        $earthRadius = 6367000; //approximate radius of earth in meters

        /*
          Convert these degrees to radians
          to work with the formula
        */

        $lat1 = ($lat1 * pi() ) / 180;
        $lng1 = ($lng1 * pi() ) / 180;

        $lat2 = ($lat2 * pi() ) / 180;
        $lng2 = ($lng2 * pi() ) / 180;

        /*
          Using the
          Haversine formula

          http://en.wikipedia.org/wiki/Haversine_formula

          calculate the distance
        */

        $calcLongitude = $lng2 - $lng1;
        $calcLatitude = $lat2 - $lat1;
        $stepOne = pow(sin($calcLatitude / 2), 2) + cos($lat1) * cos($lat2) * pow(sin($calcLongitude / 2), 2);
        $stepTwo = 2 * asin(min(1, sqrt($stepOne)));
        $calculatedDistance = $earthRadius * $stepTwo;

        return round($calculatedDistance);
    }


    /*
     *  @desc 计算停车费用
     *  @param int $parkid 停车场id
     *  @param Date $startTime 车主进场时间
     *
    */

    protected function parkingFee($startTime, $parkid){
        return $this->_parkingFee($startTime, time(), $parkid);
    }
    //实际计算方法，增加$endTime参数便于测试
    protected function _parkingFee($startTime, $endTime, $parkid, $isdebug=false){
    	$runcount = 0;
    	
        $fee = 0;
        $rulestime = M('rules_time');
        $rulesmoney = M('rules_money');
        while($startTime < $endTime && $runcount < 100){
            $timeStr = date("H:i:s",$startTime);
            //找到开始停车那个时间点所适用规则
            $con1 = "parkid=".$parkid." and startime<='".$timeStr."' and endtime>='".$timeStr."'";
            if($isdebug){
							echo $con1."\n<br>";
						}
            $ruleArr = $rulestime->where($con1)->limit(1)->select();
            if($isdebug){
							print_r($ruleArr);
							echo "\n<br>";
						}
            if(!$ruleArr || count($ruleArr) == 0){//没有合适的规则
                break;
            }
            $mins = ceil(($endTime-$startTime)/60);
            $ruleid = $ruleArr[0]['id'];
            $stopatend = $ruleArr[0]['stopatend'];
            $mins_rule = 0;
            if($stopatend){//该段规则有截止时间
                $mydaystr = date("Y-m-d",$startTime);
                $ruleend = strtotime($mydaystr.' '.$ruleArr[0]['endtime']);
                $stoptime = strtotime($mydaystr.' '.$ruleArr[0]['stoptime']);
                if($stoptime < $ruleend){//如果规则stoptime小于endtime，则认为stoptime在第二天
                    $stoptime+=24*60*60;
                }
                $mins_rule = ceil(($stoptime-$startTime)/60);
                if($isdebug){
									echo "ruleid:".$ruleid."mins:".$mins."mins_rule".$mins_rule."\n<br>";
								}
                if($mins_rule < $mins){//结算时间大于该段规则截止时间：则根据规则截止时间计算费用
                    $mins = $mins_rule;
                }
            }
            $con2 = "rulesid=".$ruleid;
            $moneyArr = $rulesmoney->where($con2)->order('mins')->select();
            $arrLength = count($moneyArr);
            $money=0;
            for($i=0;$i < $arrLength;$i++){
                if($moneyArr[$i]['mins']>=$mins){
                    $money=$moneyArr[$i]['money'];
                    if($isdebug){
                    	echo $i.":";
                    	print_r($moneyArr[$i]);
											echo "\n<br>";
										}
                    break;
                }
            }
            if($i >= $arrLength){//超过规则所支持的时长，需要用最长所支持的时间
                $money = $moneyArr[$arrLength-1]['money'];
                $mins = $moneyArr[$arrLength-1]['mins'];
                if($isdebug){
                	$i = $arrLength-1;
                	echo $i.":";
                	print_r($moneyArr[$i]);
									echo "\n<br>";
								}
            }
            $fee += $money;
            $startTime += $mins*60+1;
            if($isdebug){
							echo "money:".$money." fee:".$fee." startTime".$startTime."\n<br>\n<br>";
						}
            /*if($mins <= 0){
                dump($moneyArr);
                break;
            }*/
            $runcount++;
        }

        return $fee;
    }
    
    //计算当前时间下，用户付费可以停到的时间
	protected function _parkingEndTime($startTime, $endTime, $parkid, $isdebug=false){
		$runcount = 0;
		
		$myt = $startTime;
		$rulestime = M('rules_time');
		$rulesmoney = M('rules_money');
		while($startTime < $endTime && $runcount < 100){
			$timeStr = date("H:i:s",$startTime);
			//找到开始停车那个时间点所适用规则
			$con1 = "parkid=".$parkid." and startime<='".$timeStr."' and endtime>='".$timeStr."'";
			if($isdebug){
				echo $con1."\n<br>";
			}
			$ruleArr = $rulestime->where($con1)->limit(1)->select();
			if($isdebug){
				print_r($ruleArr);
				echo "\n<br>";
			}
			if(!$ruleArr || count($ruleArr) == 0){//没有合适的规则
				break;
			}
			$mins = ceil(($endTime-$startTime)/60);
			$ruleid = $ruleArr[0]['id'];
			$stopatend = $ruleArr[0]['stopatend'];
			$mins_rule = 0;
			if($stopatend){//该段规则有截止时间
				$mydaystr = date("Y-m-d",$startTime);
				$ruleend = strtotime($mydaystr.' '.$ruleArr[0]['endtime']);
				$stoptime = strtotime($mydaystr.' '.$ruleArr[0]['stoptime']);
				if($stoptime < $ruleend){//如果规则stoptime小于endtime，则认为stoptime在第二天
					$stoptime+=24*60*60;
				}
				$mins_rule = ceil(($stoptime-$startTime)/60);
				if($isdebug){
					echo "ruleid:".$ruleid."mins:".$mins."mins_rule".$mins_rule."\n<br>";
				}
				if($mins_rule < $mins){//结算时间大于该段规则截止时间：则根据规则截止时间计算费用
					$mins = $mins_rule;
				}
			}
			$con2 = "rulesid=".$ruleid;
			$moneyArr = $rulesmoney->where($con2)->order('mins')->select();
			$arrLength = count($moneyArr);
			$t=0;
			for($i=0;$i < $arrLength;$i++){
				if($moneyArr[$i]['mins']>=$mins){
					if($stopatend && $mins_rule < $moneyArr[$i]['mins']){
						//该段规则有截止时间，且以规则截止时间来计算
						$t = $mins_rule*60;
					}else{
						$t = $moneyArr[$i]['mins']*60;
					}
					break;
				}
			}
			if($i >= $arrLength){//超过规则所支持的时长，需要用最长所支持的时间
				$t = $moneyArr[$arrLength-1]['mins']*60;
				$mins = $moneyArr[$arrLength-1]['mins'];
			}
			$myt += $t;
			$startTime += $mins*60+1;
			/*if($mins <= 0){
                dump($moneyArr);
                break;
            }*/
      $runcount++;
		}

		return $myt;
	}
	
	protected function guid(){
    if (function_exists('com_create_guid')){
        return trim(com_create_guid(), '{}');
    }else{
        mt_srand((double)microtime()*10000);//optional for php 4.2.0 and up.
        $charid = strtoupper(md5(uniqid(rand(), true)));
        $hyphen = chr(45);// "-"
        $uuid = substr($charid, 0, 8).$hyphen
                .substr($charid, 8, 4).$hyphen
                .substr($charid,12, 4).$hyphen
                .substr($charid,16, 4).$hyphen
                .substr($charid,20,12);
        return $uuid;
    }
	}

	//生成红包
	//返回值：
	//null		创建失败
	//string	礼包码
	protected function _createGiftPack($type, $uid, $starttime, $endtime, $coupon_starttime, $coupon_endtime, $minmoney, $maxmoney, $maxnum, $info=''){
		$code = $this->guid();
		if($type == 0){
			//随机红包
			$giftpack = M('driver_giftpack');
			$data = array('code'=>$code,'type'=>$type,'uid'=>$uid,'starttime'=>$starttime, 'endtime'=>$endtime, 'coupon_starttime'=>$coupon_starttime, 'coupon_endtime'=>$coupon_endtime, 'minmoney'=>$minmoney, 'maxmoney'=>$maxmoney, 'maxnum'=>$maxnum, 'info'=>$info);
			$giftpack->add($data);
			
			return $code;
		}
		return null;
	}
	
	//纪录红包使用日志
	//返回值：
	//null		创建失败
	//string	礼包码
	protected function _saveGiftLog($code, $optype, $uid, $fromid){
		$fromip = $_SERVER["REMOTE_ADDR"];
		
		$giftlog = M('driver_giftlog');
		$data = array('code'=>$code,'optype'=>$optype,'uid'=>$uid,'fromid'=>$fromid, 'fromip'=>$fromip);
		$giftlog->add($data);
	}
	
	//生成折扣劵
	//返回值：
	//array			折扣劵信息
	protected function _createCoupon($uid, $type, $money, $starttime, $endtime, $source){
		$coupon = M('driver_coupon');
		$data = array('type'=>$type,'uid'=>$uid,'starttime'=>$starttime, 'endtime'=>$endtime, 'money'=>$money, 'source'=>$source);
		$data['id'] = $coupon->add($data);
		return $data;
	}
	
	//生成1元折扣劵
	//返回值：
	//array			折扣劵信息
	protected function _createCoupon1($uid, $starttime, $endtime){
		return $this->_createCoupon($uid, -1, 0, $starttime, $endtime, 0);
	}
	
	//检查红包
	//返回值：
	//0				没有合适的红包
	//-1			已领完
	//-2			活动还没开始
	//-3			活动已结束
	//-4			已领取过该红包
	//array		红包信息
	protected function _checkGiftPack($code,$uid=0){
		$giftpack = M('driver_giftpack');
		$con1 = array("code"=>$code);
		$giftArr = $giftpack->where($con1)->limit(1)->select();
		if(!$giftArr || count($giftArr) == 0){//没有合适的红包
			return 0;
		}
		else if($giftArr[0]['maxnum']<=$giftArr[0]['num']){//已领完
			return -1;
		}
		$starttime = strtotime($giftArr[0]['starttime']);
		$endtime = strtotime($giftArr[0]['endtime']);
		$now = time();
		if($now < $starttime){//还没开始
			return -2;
		}
		else if($now > $endtime){//已结束
			return -3;
		}
		$id = $giftArr[0]['id'];
		if($uid != 0 && $uid != 1){//已领取过该红包？
			$coupon = M('driver_coupon');
			$con1 = array('uid'=>$uid,'source'=>$id);
			if($coupon->where($con1)->find()){
				return -4;
			}
		}
		return $giftArr[0];
	}
	
	//使用红包来获得折扣劵
	//返回值：
	//0				没有合适的红包
	//-1			已领完
	//-2			活动还没开始
	//-3			活动已结束
	//-4			已领取过该红包
	//array			折扣劵信息
	protected function _useGiftPack($uid, $code){
		$giftpack = M('driver_giftpack');
		$con1 = "code='".$code."' and (uid=0 or uid=".$uid.")";
		$giftArr = $giftpack->where($con1)->limit(1)->select();
		if(!$giftArr || count($giftArr) == 0){//没有合适的红包
			return 0;
		}
		else if($giftArr[0]['maxnum']<=$giftArr[0]['num']){//已领完
			return -1;
		}
		$starttime = strtotime($giftArr[0]['starttime']);
		$endtime = strtotime($giftArr[0]['endtime']);
		$now = time();
		if($now < $starttime){//还没开始
			return -2;
		}
		else if($now > $endtime){//已结束
			return -3;
		}
		$id = $giftArr[0]['id'];
		if($uid != 1){//已领取过该红包？
			$coupon = M('driver_coupon');
			$con1 = array('uid'=>$uid,'source'=>$id);
			if($coupon->where($con1)->find()){
				return -4;
			}
		}
		$giftpack->where(array('id'=>$id))->setInc('num',1);//计数器＋1
		return $this->_createCoupon($uid, $giftArr[0]['type'], rand($giftArr[0]['minmoney'],$giftArr[0]['maxmoney']), $giftArr[0]['coupon_starttime'], $giftArr[0]['coupon_endtime'], $id);
	}
	
	//列出可用折扣劵
	//返回值：
	//array			折扣劵列表（二维）
	protected function _listCoupon($uid,$all){
		$nowStr = date("Y-m-d H:i:s");
		$coupon = M('driver_coupon');
        $couponArr = array();
        //$oneMonth = date("Y-m-d H:i:s", strtotime("-1 months", time()));
        if($all){
            $con1 = "uid=".$uid." and starttime<='".$nowStr."' and endtime>='".$nowStr."' ";
            $couponArr1 = $coupon->where($con1)->order('status asc, endtime asc')->select();

            $con2 = "uid=".$uid." and starttime<='".$nowStr."' and endtime<'".$nowStr."' ";
            $couponArr2 = $coupon->where($con2)->order('endtime desc')->limit(10)->select();

            //array_merge的数组一定不能为空
            if (is_array($couponArr1) && is_array($couponArr2)) {
                $couponArr = array_merge($couponArr1, $couponArr2);
            }
            elseif(is_array($couponArr1)){
                $couponArr = $couponArr1;
            }
            else{
                $couponArr = $couponArr2;
            }

        }
        else{
            $con1 = "uid=".$uid." and starttime<='".$nowStr."' and endtime>='".$nowStr."' and status=0";
            $couponArr = $coupon->where($con1)->order('type asc,money desc,endtime asc')->select();
        }

		return $couponArr;
	}
	
	//使用折扣劵
	//返回值：
	//0				抵用劵不存在
	//-1			已领完
	//-2			活动还没开始
	//-3			活动已结束
	//int			抵扣金额
	protected function _useCoupon($uid, $id, $bill){
		$coupon = M('driver_coupon');
		$con1 = array('id'=>$id,'uid'=>$uid);
		$couponArr = $coupon->where($con1)->limit(1)->select();
		if(!$couponArr || count($couponArr) == 0){//抵用劵不存在
			return 0;
		}
		else if($couponArr[0]['status']!=0){//已领完
			return -1;
		}
		$starttime = strtotime($couponArr[0]['starttime']);
		$endtime = strtotime($couponArr[0]['endtime']);
		$now = time();
		if($now < $starttime){//还没开始
			return -2;
		}
		else if($now > $endtime){//已结束
			return -3;
		}
		$coupon->where(array('id'=>$id))->setInc('status',1);//计数器＋1
		if($couponArr[0]['type'] ==  -1){//1元折扣劵
			return $bill>1?$bill-1:$bill-0.01;
		}
		return $couponArr[0]['money']<$bill?$couponArr[0]['money']:$bill-0.01;
	}
	
	//检查折扣劵
	//返回值：
	//0				抵用劵不存在
	//-1			已领完
	//-2			活动还没开始
	//-3			活动已结束
	//int			抵扣金额
	protected function _checkCoupon($uid, $id, $bill){
		$coupon = M('driver_coupon');
		$con1 = array('id'=>$id,'uid'=>$uid);
		$couponArr = $coupon->where($con1)->limit(1)->select();
		if(!$couponArr || count($couponArr) == 0){//抵用劵不存在
			return 0;
		}
		else if($couponArr[0]['status']!=0){//已领完
			return -1;
		}
		$starttime = strtotime($couponArr[0]['starttime']);
		$endtime = strtotime($couponArr[0]['endtime']);
		$now = time();
		if($now < $starttime){//还没开始
			return -2;
		}
		else if($now > $endtime){//已结束
			return -3;
		}
		if($couponArr[0]['type'] == -1){//1元折扣劵
			return $bill>1?$bill-1:$bill-0.01;
		}
		return $couponArr[0]['money']<$bill?$couponArr[0]['money']:$bill-0.01;
	}
	
	//不检查，直接消耗折扣劵
	protected function _consumeCoupon($id){
		$coupon = M('driver_coupon');
		$coupon->where(array('id'=>$id))->setInc('status',1);//计数器＋1
	}
	
	protected function getuiPush($parkid,$isIn,$title,$txt){
		include_once(dirname(__FILE__) . '/../Common/getui/' . 'IGt.Push.php');
		
    	/*推送*/
			$msg = json_encode(array('t'=>$isIn?'in':'out'));
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
	
    protected function simulateEnter($uid, $pid, $endtime, $isalert)
    {
    	$Order = M('ParkOrder');
    	$arr = array();
      $arr['uid'] = $uid;
      $arr['pid'] = $pid;
      $arr['state'] = 0;
      $arr['startime'] = date("Y-m-d H:i:s");
      $arr['endtime'] = date("Y-m-d H:i:s",$endtime);
      $arr['creater'] = 0;
      $arr['createtime'] = date("Y-m-d H:i:s");
      $arr['updater'] = 0;
      $oid = $Order->add($arr);
      
      if($isalert){
      	include_once(dirname(__FILE__) . '/../Conf/' . 'config_simulation.php');
      	
      	$this->getuiPush($pid, true, $config_simulation_in[$uid]['title'], $config_simulation_in[$uid]['txt']);
      }
    	
    	return $oid;
    }
    
    protected function simulateLeave($oid, $endtime, $isalert)
    {
    	$Order = M('ParkOrder');
    	$Order->where(array('id'=>$oid,'uid'=>array('elt',0)))->save(array('state'=>2,'endtime'=>date("Y-m-d H:i:s", $endtime)));
      
      if($isalert){
      	include_once(dirname(__FILE__) . '/../Conf/' . 'config_simulation.php');
      	$orderData = $Order->where(array('id'=>$oid,'uid'=>array('elt',0)))->find();
      	$pid = $orderData['pid'];
      	$uid = $orderData['uid'];
      	$this->getuiPush($pid, false, $config_simulation_out[$uid]['title'], $config_simulation_out[$uid]['txt']);
      }
    }


    //验证openid是否有效
    //返回值：
    //0		    无效
    //1			有效
    protected function _validOpenid($uid){

        $DriverInfo = M('DriverInfo');
        $map = array();
        $map['id'] = $uid;
        $openid = $DriverInfo->where($map)->getField('openid');

        $OpenidValid = M('OpenidValid');
        $map = array();
        $map['openid'] = $openid;
        $result = $OpenidValid->where($map)->find();
        if(empty($result)){
            return 0;
        }
        else{
            $data = array();
            $data['id'] = $result['id'];
            $data['valid'] = 1;
            $OpenidValid->save($data);
            return 1;
        }

    }
  
  
  
  /**wx jsapi**/
  protected function getSignPackage($url) {
  	include_once(dirname(__FILE__) . '/../Common/Weixin/WxPay/' . 'WxPay.pub.config.php');
  	
    $jsapiTicket = $this->getJsApiTicket();

    $timestamp = time();
    $nonceStr = $this->createNonceStr();

    // 这里参数的顺序要按照 key 值 ASCII 码升序排序
    $string = "jsapi_ticket=$jsapiTicket&noncestr=$nonceStr&timestamp=$timestamp&url=$url";

    $signature = sha1($string);

    $signPackage = array(
      "appId"     => WxPayConf_pub::APPID,
      "nonceStr"  => $nonceStr,
      "timestamp" => $timestamp,
      "url"       => $url,
      "signature" => $signature,
      "rawString" => $string
    );
    return $signPackage; 
  }
  
  private function createNonceStr($length = 16) {
    $chars = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789";
    $str = "";
    for ($i = 0; $i < $length; $i++) {
      $str .= substr($chars, mt_rand(0, strlen($chars) - 1), 1);
    }
    return $str;
  }
  
  private function getJsApiTicket() {
    // jsapi_ticket 应该全局存储与更新
    $key = "wx_jsapi_ticket.json";
    $data = S($key);
    if (!$data || $data->expire_time < time()) {
    	include_once(dirname(__FILE__) . '/../Common/Weixin/WxPay/' . 'WxPay.pub.config.php');
      $accessToken = $this->getAccessToken();
      // 如果是企业号用以下 URL 获取 ticket
      // $url = "https://qyapi.weixin.qq.com/cgi-bin/get_jsapi_ticket?access_token=$accessToken";
      $url = "https://api.weixin.qq.com/cgi-bin/ticket/getticket?type=jsapi&access_token=".$accessToken;
      $res = json_decode($this->httpGet($url));
      $ticket = $res->ticket;
      if ($ticket) {
        $data = array('expire_time' => time() + 7000,'jsapi_ticket' => $ticket);
        S($key,$data,C('DATA_CACHE_TIME'));
      }
    } else {
      $ticket = $data->jsapi_ticket;
    }

    return $ticket;
  }
  private function getAccessToken() {
    // access_token 应该全局存储与更新
    $key = "wx_access_token.json";
    $data = S($key);
    if (!$data || $data->expire_time < time()) {
    	include_once(dirname(__FILE__) . '/../Common/Weixin/WxPay/' . 'WxPay.pub.config.php');
      // 如果是企业号用以下URL获取access_token
      // $url = "https://qyapi.weixin.qq.com/cgi-bin/gettoken?corpid=$this->appId&corpsecret=$this->appSecret";
      $url = "https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&appid=".WxPayConf_pub::APPID."&secret=".WxPayConf_pub::APPSECRET;
      $res = json_decode($this->httpGet($url));
      $access_token = $res->access_token;
      if ($access_token) {
        $data = array('expire_time' => time() + 7000,'access_token' => $access_token);
        S($key,$data,C('DATA_CACHE_TIME'));
      }
    } else {
      $access_token = $data->access_token;
    }
    return $access_token;
  }

  private function httpGet($url) {
    $curl = curl_init();
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($curl, CURLOPT_TIMEOUT, 500);
    curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
    curl_setopt($curl, CURLOPT_URL, $url);

    $res = curl_exec($curl);
    curl_close($curl);

    return $res;
  }
}
