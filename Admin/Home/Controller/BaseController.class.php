<?php
/**
 * Created by PhpStorm.
 * User: Bin
 * Date: 15/3/4
 * Time: 下午4:29
 */

class BaseController extends \Think\Controller {


    /**
     * 检测验证码
     * @param  integer $id 验证码ID
     * @return boolean     检测结果
     */
    function check_verify($code, $id = 1){
        $verify = new \Think\Verify();
        return $verify->check($code, $id);
    }

    /**
     * 检测用户是否登录
     * @return integer 0-未登录，大于0-当前登录用户ID
     */
    function is_login(){
        $user = session('admin_auth');
        if (empty($user)) {
            return 0;
        } else {
            return  $user['uid'];
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
*  @desc $gid 获取礼品类型
*  @param $gid
*/
    protected function getGiftType($gid)
    {
        $ParkInfo = M('GiftList');
        $map = array();
        $map['id'] = $gid;
        $giftData = $ParkInfo->where($map)->find();
        if(!empty($giftData)) {
            return  $giftData['type'];
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
     *  @desc 根据uid 获取车主
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

    //获取红包的简介
    protected  function  getGiftInfo($code){
        $DriverGiftPack = M('DriverGiftpack');
        $map = array();
        $map['code'] = $code;
        $giftInfo = $DriverGiftPack->where($map)->getField('info');

        return $giftInfo;

    }

    //生产红包code
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


    //每消耗一张折扣券，再自动发一个红包给这个用户
    protected function autoSendGift($uid){

        //1.获取uid
        $DriverCoupon = M('DriverCoupon');


        //2.获取已领过的红包id数组
        $map = array();
        $map['uid'] = $uid;
        $giftArr  = $DriverCoupon->where($map)->getField('source',true);

        //3.遍历自动红包数组，拿到最后发送的红包ID
        $autoArr = C('AUTO_GIFT');
        for($i = 0; $i < count($autoArr); $i++){
            if(in_array($autoArr[$i], $giftArr)){
                continue;
            }
            else{
                if($i == count($autoArr)-3){
                    sendMail('dubin@duduche.me',"[自动红包-紧张]", "自动红包已经发送到倒数第二个了，请尽快补充！红包ID：".$autoArr[$i]);
                }
                if($i == count($autoArr)-1){
                    sendMail('dubin@duduche.me',"[自动红包-紧张]", "自动红包已经发完，请尽快补充！红包ID：".$autoArr[$i]);
                    return null;
                }
                break;
            }
        }

        //4.根据gid获取hcode
        $gid = $autoArr[$i];
        $GiftPack = M('DriverGiftpack');
        $map = array();
        $map['id'] = $gid;
        $hcode = $GiftPack->where($map)->getField('code');
        $fromid = C('AUTO_FROM_ID');

        //5.获取推送的红包短URL
        if(!empty($hcode)){
            $myurl = "http://driver.duduche.me/driver.php/home/weixin/giftCallBack/type/10/hcode/$hcode/fromid/$fromid/";
            $myurl = urlencode($myurl);
            $lineLink = "https://open.weixin.qq.com/connect/oauth2/authorize?appid=wxd417c2e70f817f89&redirect_uri=$myurl&response_type=code&scope=snsapi_base#wechat_redirect";
            $tinyurl = $this->tinyurl($lineLink);
        }
        else{
            sendMail('dubin@duduche.me',"[自动红包错误]", "获取HCODE错误，GID：".$gid);
            return null;
        }

        //6.生成发送内容需要的参数
        $openid = $this->getOpenID($uid);
        $contentArr = C('AUTO_GIFT_MSG');
        if(empty($tinyurl)){
            $content = $contentArr[$i%count($contentArr)]." <a href='".$lineLink."'>点击领取>></a>";
        }
        else{
            $content = $contentArr[$i%count($contentArr)]." <a href='".$tinyurl."'>$tinyurl</a>";
        }

        //7.发送消息模板给用户的公共号
        $msg_json =  sprintf ( C('CUSTOM_TEXT_TPL'), $openid, $content);

        $result = $this->pushMsg($msg_json);
        $result_array = json_decode($result,TRUE);
        if($result_array['errcode'] !=0){
            sendMail('dubin@duduche.me', "[自动红包错误]", "发送错误, 错误码：".$result_array['errcode']." Openid：$openid, Content:$content");
            return null;
        }

        return $hcode;

    }

    //获取百度短链接
    public function tinyurl($url = ""){
        $ch=curl_init();
        curl_setopt($ch,CURLOPT_URL,"http://dwz.cn/create.php");
        curl_setopt($ch,CURLOPT_POST,true);
        curl_setopt($ch,CURLOPT_RETURNTRANSFER,true);
        $data=array('url'=> $url);
        curl_setopt($ch,CURLOPT_POSTFIELDS,$data);
        $strRes=curl_exec($ch);
        curl_close($ch);
        $arrResponse=json_decode($strRes,true);
        $result = array();
        if($arrResponse['status']==0)
        {
            $tinyurl= $arrResponse['tinyurl'];
            return $tinyurl;
        }
        else{

            return null;
        }

    }

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

    /*
        *  @desc 向指定的用户发送消息
        *  @param uid 推送的用户名
        *  @param msg 推送的消息体
       */
    protected function pushNotice($uid, $msg, $tran){
        $Driver = M('DriverInfo');
        $driver = $Driver->where(array('id'=>$uid))->find();
        //推送微信用户
        if(!empty($driver['openid'])){
            $msg_json =  sprintf ( C('CUSTOM_TEXT_TPL'), $driver['openid'], $msg);
            $this->pushMsg($msg_json);
        }
        //推送APP用户
        if(!empty($driver['pushid'])){
            $this->getuiPush($driver['pushid'], $msg, $tran);
        }
    }

    /*
     *  @desc 利用个推向指定的APP用户发送消息
     *  @param pushid 推送的用户机器号
     *  @param msg 推送的消息体
     *  @param tran 透传内容
    */
    protected function getuiPush($pushid, $msg, $tran){
        include_once(dirname(__FILE__) . '/../Common/getui/' . 'IGt.Push.php');
        /*推送*/
        $igt = new IGeTui(C('GETUI')['GT_HOST'],C('GETUI')['GT_APPKEY'],C('GETUI')['GT_MASTERSECRET']);
        //接收方
        $target = new IGtTarget();
        $target->set_appId(C('GETUI')['GT_APPID']);
        $target->set_clientId($pushid);
        //个推popup消息
        $template = $this->IGtNotificationTemplateDemo('嘟嘟停车', $msg, $tran);
        $message = new IGtSingleMessage();
        $message->set_isOffline(true);//是否离线
        $message->set_offlineExpireTime(3600*12*1000);//离线时间
        $message->set_data($template);//设置推送消息类型
        //$message->set_PushNetWorkType(0);	//设置是否根据WIFI推送消息，1为wifi推送，0为不限制推送
        //$rep = $igt->pushMessageToSingle($message, $target);
        //var_dump($rep);
        try {
            $rep = $igt->pushMessageToSingle($message, $target);
        }catch(RequestException $e){
            $requstId =e.getRequestId();
            $rep = $igt->pushMessageToSingle($message, $target,$requstId);
        }
        //dump($rep);
        //dump($pushid);
    }

    //个推通知消息模板
    protected function IGtNotificationTemplateDemo($title, $notice, $msg){
        $template =  new IGtNotificationTemplate();
        $template->set_appId(C('GETUI')['GT_APPID']);//应用appid
        $template->set_appkey(C('GETUI')['GT_APPKEY']);//应用appkey
        $template->set_transmissionType(1);//透传消息类型
        $template->set_transmissionContent($msg);//透传内容
        $template->set_title($title);//通知栏标题
        $template->set_text($notice);//通知栏内容
        $template->set_logo('icon.png');//通知栏logo
        $template->set_logoURL("http://duduche.me/html/userhtml/img/icon.png");//通知栏logo链接
        $template->set_isRing(true);//是否响铃
        $template->set_isVibrate(true);//是否震动
        $template->set_isClearable(true);//通知栏是否可清除
        // iOS推送需要设置的pushInfo字段
        //$template ->set_pushInfo($actionLocKey,$badge,$message,$sound,$payload,$locKey,$locArgs,$launchImage);
        //$template ->set_pushInfo("test",1,"message","","","","","");
        //$template->set_pushInfo(null, 1, $notice, "sound", $msg, null, null, null); // iOS参数
        return $template;
    }

    //个推透传消息模板
    protected function IGtTransmissionTemplateDemo($title, $notice, $msg){
        $template =  new IGtTransmissionTemplate();
        $template->set_appId(C('GETUI')['GT_APPID']);//应用appid
        $template->set_appkey(C('GETUI')['GT_APPKEY']);//应用appkey
        $template->set_transmissionType(1);//透传消息类型
        $template->set_transmissionContent($msg);//透传内容
        //iOS推送需要设置的pushInfo字段
        //$template ->set_pushInfo($actionLocKey,$badge,$message,$sound,$payload,$locKey,$locArgs,$launchImage);
        //$template ->set_pushInfo("", 0, "", "", "", "", "", "");
        //$template->set_pushInfo(null, 1, null, "sound", "", $notice, null, null); // iOS参数
        $template->set_pushInfo(null, 1, $notice, "sound", $msg, null, null, null); // iOS参数
        return $template;
    }

    /*
     *  @desc 利用客户接口向指定的微信用户发送消息
     *  @param msg 推送的消息体
    */
    protected function pushMsg($msg){
        $token = $this->getToken();
        $post_url = C('WX_CUSTOM_URL').$token;

        return $this->doCurlPostRequest($post_url,$msg);

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

    /**
     *  @desc 检查今天是否已经发送过自动红包，未发送过，则缓存
     *  @param $list 已发送缓存列表
     */
    protected function cacheAutoGift($list = -1){
        $timeStr = date('Ymd');
        $key = 'AutoGift_'.$timeStr;
        if($list == -1){
            return S($key);
        }
        else{
            S($key, $list, 24*60*60);
            return $list;
        }
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
}
