<?php
/**
 * 后台基础控制器
 * @Bin
 */
class BaseController extends \Think\Controller {

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

    protected function sendmsg($code, $data){
        $result = array(
                    'code'=>$code,
                    'data'=>$data
                );

        $this->ajaxReturn($result,'jsonp');
    }

    
    protected function ajaxOk($data){
        $this->sendmsg(0,$data);
        exit;
    }
    protected function ajaxMsg($msg){
        $this->sendmsg(10,$msg);
        exit;
    }
    protected function ajaxFail(){
        $this->sendmsg(100,"");
        exit;
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
    protected function _parkingFee($startTime, $endTime, $parkid){
        $fee = 0;
        $rulestime = M('rules_time');
        $rulesmoney = M('rules_money');
        while($startTime < $endTime){
            $timeStr = date("H:i:s",$startTime);
            //找到开始停车那个时间点所适用规则
            $con1 = "parkid=".$parkid." and startime<='".$timeStr."' and endtime>='".$timeStr."'";
            $ruleArr = $rulestime->where($con1)->limit(1)->select();
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
    
    //计算当前时间下，用户付费可以停到的时间
	protected function _parkingEndTime($startTime, $endTime, $parkid){
		$myt = $startTime;
		$rulestime = M('rules_time');
		$rulesmoney = M('rules_money');
		while($startTime < $endTime){
			$timeStr = date("H:i:s",$startTime);
			//找到开始停车那个时间点所适用规则
			$con1 = "parkid=".$parkid." and startime<='".$timeStr."' and endtime>='".$timeStr."'";
			$ruleArr = $rulestime->where($con1)->limit(1)->select();
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
					if($stopatend){
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
			$startTime += $mins*60;
			/*if($mins <= 0){
                dump($moneyArr);
                break;
            }*/
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
	protected function _listCoupon($uid){
		$nowStr = date("Y-m-d H:i:s");
		$coupon = M('driver_coupon');
		$con1 = "uid=".$uid." and starttime<='".$nowStr."' and endtime>='".$nowStr."' and status=0";
		$couponArr = $coupon->where($con1)->order('type asc,money desc')->select();
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
		if($couponArr[0]['type'] == -1){//1元折扣劵
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
	
}
