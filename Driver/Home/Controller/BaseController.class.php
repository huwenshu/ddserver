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

        $cachekey = $this->getCacheKey($uid);



        S($cachekey,array(
            'uid' => $uid,
            'uuid' =>$uuid,
        ),C('DATA_CACHE_TIME'));

        return $uuid;
    }

    protected function getCacheKey($uid){
        return '____usercachekey___'.$uid;
    }

    protected function getUsercache($uid){
        $key = $this->getCacheKey($uid);
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
