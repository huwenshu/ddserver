<?php
/**
 * 后台基础控制器
 * @Bin
 */
class BaseController extends \Think\Controller {

    protected function createUUID($uid, $data){
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
            'data' =>$data,
        ),C('DATA_CACHE_TIME'));

        return $uuid;
    }

    protected function getCacheKey($uid){
        return '____parkadmincachekey___'.$uid;
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
     *  @desc 根据adid 获取管理员名字
     *  @param $adid
     */
    protected function getAdmin($adid)
    {
        $ParkAdmin = M('ParkAdmin');
        $map = array();
        $map['id'] = $adid;
        $adminData = $ParkAdmin->where($map)->find();
        if(!empty($adminData)) {
            return  $adminData['name'];
        }
        else{
            return null;
        }

    }
    /**
     *  @desc 根据adid 获取管理员昵称
     *  @param $adid
     */
    protected function getAdminNick($adid)
    {
        $ParkAdmin = M('ParkAdmin');
        $map = array();
        $map['id'] = $adid;
        $adminData = $ParkAdmin->where($map)->find();
        if(!empty($adminData)) {
            return  $adminData['nickname'];
        }
        else{
            return "系统";
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
     *  @desc $gid 获取礼品名字
     *  @param $gid
     */
    protected function getGiftName($gid)
    {
        $ParkInfo = M('GiftList');
        $map = array();
        $map['id'] = $gid;
        $giftData = $ParkInfo->where($map)->find();
        if(!empty($giftData)) {
            return  $giftData['name'];
        }
        else{
            return null;
        }

    }

    /**
     *  @desc $gid 获取礼品
     *  @param $gid
     */
    protected function getGift($gid)
    {
        $ParkInfo = M('GiftList');
        $map = array();
        $map['id'] = $gid;
        $giftData = $ParkInfo->where($map)->find();
        if(!empty($giftData)) {
            return  $giftData;
        }
        else{
            return null;
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
     *  @desc 积分方法
     *  @param int $aid 管理员id
     *  @param int $add 增加的积分
     */
    protected function addScore($aid, $add){

        $ParkAdmin = M('ParkAdmin');
        $map = array();
        $map['id'] = $aid;
        $addData = $ParkAdmin->where($map)->save(array('score'=>array('exp','score+'.$add),'lastop'=>time()));

        if(empty($addData)){
            return false;
        }
        else{
            return true;
        }
    }

    /**
     *  @desc 检查奖励积分，未超过，则缓存
     *  @param int $uid 管理员id
     *  @param int $add 增加的积分
     */
    protected function cacheScore($uid,$add){

        $timeStr = date('Ymd');
        $key = 'score_'.$uid.'_'.$timeStr;
        $score = S($key);
        dump($score);
        $limit = C('SCORE_LIMIT');
        if(!$score){//不存在cache
            if($add > $limit){
                return false;
            }
            else{
                $score = $add;
                S($key, $score, 24*60*60);
            }
        }
        else{
            $score = $score + $add;
            if($score > $limit){
                return false;
            }
            else{
                S($key, $score, 24*60*60);
            }
        }

        return true;

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
     *  @desc  二位数组去重
     *  @param $arr 去重的数组
     *  @param $key   去除的子列
     */
    protected function  assoc_unique($arr, $key){
        $tmp_arr = array();
        foreach($arr as $k => $v)
        {
            if(in_array($v[$key], $tmp_arr))//搜索$v[$key]是否在$tmp_arr数组中存在，若存在返回true
            {
                unset($arr[$k]);
            }
            else {
                $tmp_arr[] = $v[$key];
            }
        }

        return $arr;
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
    
    //计算当前时间下，用户结算后可以停到的时间
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

    /*
     *  @desc 利用模板接口向指定的微信用户发送消息
     *  @param msg 推送的消息体
    */
    protected function noticeMsg($msg){
        $token = $this->getToken();
        $post_url = C('WX_TEMPLATE_URL').$token;

        return $this->doCurlPostRequest($post_url,$msg);

    }


    //获取实时Token
    protected function getToken(){
        $WeixinToken = M('WeixinToken');
        $map = array();
        $map['appid'] = C('APPID');
        $map['type']  = 0;
        $WToken = $WeixinToken->where($map)->find();
        if(is_array($WToken)){
            $token = $WToken['token'];
            $expire = $WToken['expire'];
            $addTimestamp = $WToken['addtimestamp'];
            $current = time();
            if($addTimestamp + $expire - 30 > $current) {
                return $token;//返回缓存的数据
            }
        }

        //数据失效，重新获取
        $para = array(
            "grant_type" => "client_credential",
            "appid" => C('APPID'),
            "secret" => C('APPSECRET')
        );
        $url = C('WX_API_URL')."token";
        $ret = $this->doCurlGetRequest($url, $para);
        $retData = json_decode($ret, true);
        $token = $retData['access_token'];
        $expire = $retData['expires_in'];
        $current = time();

        $data = array();
        $data['appid'] = C('APPID');
        $data['type'] = 0;
        $data['token'] = $token;
        $data['expire'] = $expire;
        $data['addTimestamp'] = $current;

        $WeixinToken->where($map)->delete();
        $WeixinToken->add($data);

        return $token;

    }

    /**
     * @desc 封装curl的调用接口，post的请求方式
     */
    protected function doCurlPostRequest($url, $requestString, $timeout = 5) {
        if($url == "" || $requestString == "" || $timeout <= 0){
            return false;
        }

        $con = curl_init((string)$url);
        curl_setopt($con, CURLOPT_HEADER, false);
        curl_setopt($con, CURLOPT_POSTFIELDS, $requestString);
        curl_setopt($con, CURLOPT_POST, true);
        curl_setopt($con, CURLOPT_RETURNTRANSFER,true);
        curl_setopt($con, CURLOPT_TIMEOUT, (int)$timeout);

        return curl_exec($con);
    }

    /**
     * @desc 封装curl的调用接口，get的请求方式
     */
    protected function doCurlGetRequest($url, $data = array(), $timeout = 10) {
        if($url == "" || $timeout <= 0){
            return false;
        }
        if($data != array()) {
            $url = $url . '?' . http_build_query($data);
        }
        $con = curl_init((string)$url);
        curl_setopt($con, CURLOPT_HEADER, false);
        curl_setopt($con, CURLOPT_RETURNTRANSFER,true);
        curl_setopt($con, CURLOPT_TIMEOUT, (int)$timeout);
        return curl_exec($con);
    }

    protected function wphp_urlencode($data) {
        if (is_array($data) || is_object($data)) {
            foreach ($data as $k => $v) {
                if (is_scalar($v)) {
                    if (is_array($data)) {
                        $data[$k] = urlencode($v);
                    } else if (is_object($data)) {
                        $data->$k = urlencode($v);
                    }
                } else if (is_array($data)) {
                    $data[$k] = $this->wphp_urlencode($v); //递归调用该函数
                } else if (is_object($data)) {
                    $data->$k = $this->wphp_urlencode($v);
                }
            }
        }
        return $data;
    }

    protected function ch_json_encode($data) {
        $ret = $this->wphp_urlencode($data);
        $ret = json_encode($ret);
        return urldecode($ret);
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

}
