<?php

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