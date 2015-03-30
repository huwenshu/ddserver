<?php
use Think\Controller;
class WeixinController extends BaseController {
    public function index(){
		if($this->checkSignature()) {
			if($_GET["echostr"]) {
				echo $_GET["echostr"];
				$this->_exit();
			}

		} else {
			$this->_exit();
		}
		$postStr = file_get_contents ( "php://input" );
		if (!empty ( $postStr )) {
			$postObj = simplexml_load_string ( $postStr, 'SimpleXMLElement', LIBXML_NOCDATA );
			if(NULL == $postObj) {
				$this->_exit();
			}
			else{
				$this->process($postObj);
			}
		}
        $this->_exit();
    }




    public function creatMenu(){
    	$token = $this->getToken();
    	$url = C('WX_API_URL')."menu/create?access_token=".$token;
    	$ret = $this->doCurlPostRequest($url, $this->ch_json_encode(C('MENU')));
    	$retData = json_decode($ret, true);
    	if($retData){
    		echo "MENU create success！";
    	}
    	else{
    		echo "MENU create fail";
    	}
        $this->_exit();
    }


    public function getJsConfig($url){
        $noncestr = $this->getRandChar(16);
        $jsapi_ticket = $this->getJsTicket();
        $timestamp = time();
        if($jsapi_ticket){
            $string1 = "jsapi_ticket=".$jsapi_ticket."&noncestr=".$noncestr."&timestamp=".$timestamp."&url=".$url;
            $signature = sha1($string1);
            $map = array();
            $map['appId'] = C('APPID');
            $map['timestamp'] = $timestamp;
            $map['nonceStr'] = $noncestr;
            $map['signature'] = $signature;
            $this->ajaxOk($map);

        }
        else{
            $this->ajaxOk(null);
        }
    }

    protected function getJsTicket(){
        $WeixinToken = M('WeixinToken');
        $map = array();
        $map['appid'] = C('APPID');
        $map['type']  = 1;
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
        $access_token = $this->getToken();

        $para = array(
            "access_token" => $access_token,
            "type" => "jsapi"
        );

        $url = C('WX_API_URL')."ticket/getticket";
        $ret = $this->doCurlGetRequest($url, $para);
        $retData = json_decode($ret, true);
        if($retData['errcode'] == 0){
            $token = $retData['ticket'];
            $expire = $retData['expires_in'];
            $current = time();
            $data = array();
            $data['appid'] = C('APPID');
            $data['type'] = 1;
            $data['token'] = $token;
            $data['expire'] = $expire;
            $data['addTimestamp'] = $current;

            $WeixinToken->where($map)->delete();
            $WeixinToken->add($data);

            return $token;
        }
        else{
            return false;
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



    protected function checkSignature()
	{
		$signature = $_GET["signature"];
		$timestamp = $_GET["timestamp"];
		$nonce = $_GET["nonce"];
		$token = C('WEIXIN_TOKEN');
		$tmpArr = array($token, $timestamp, $nonce);
		sort($tmpArr,SORT_STRING);
		$tmpStr = implode( $tmpArr );
		$tmpStr = sha1( $tmpStr );
		if( $tmpStr == $signature ){
			return true;
		}else{
			return false;
		}
	}

	//处理微信消息
	protected function process($postObj){

		$_openid  =  (string)trim($postObj->FromUserName);
		$_msgType =  (string)trim($postObj->MsgType);

		$nearURL = "http://duduche.me/driver.php/home/weixin/redirectURL/m/near/openid/".$_openid;
		$findURL = "http://duduche.me/driver.php/home/weixin/redirectURL/m/find/openid/".$_openid;
		$feeURL  = "http://duduche.me/driver.php/home/weixin/redirectURL/m/fee/openid/".$_openid;

		if ($_msgType == 'event') {
			$_event = (string)$postObj->Event;
			$_eventKey = (string)$postObj->EventKey;
			if($_event == 'subscribe'){
				$content = '欢迎您关注嘟嘟停车！我们专注于解决您的停车难问题。
嘟嘟停车有如下功能，请点击下面的链接使用：

1-找附近的空车位：<a href="'.$nearURL.'">点击这里>></a>

2-搜索地址找空车位：<a href="'.$findURL.'">点击这里>></a>

3-停车缴费：<a href="'.$feeURL.'">点击这里>></a>

目前只在上海提供服务，其他城市正在准备中。';
				$resultStr = sprintf ( C('HINT_TPL'), $_openid, C('USERNAME_WEIXIN'), time(), 'text', $content );
				echo  $resultStr;
			}
		}
		else{
			$content = '谢谢您的反馈，我们会努力做的更好！';
			$resultStr = sprintf ( C('HINT_TPL'), $_openid,  C('USERNAME_WEIXIN'), time(), 'text', $content );
			//Think\Log::write($resultStr,'ERR');
			echo  $resultStr;
		}

	}

    //处理公共号跳转，获取openid
    public  function menuCallBack($code,$state = 'near'){
        //访问微信接口，获取openid
        $URL = 'https://api.weixin.qq.com/sns/oauth2/access_token?appid='.C('APPID').'&secret='.C('APPSECRET').'&code='.$code.'&grant_type=authorization_code';
        $data = json_decode($this->doCurlGetRequest($URL),true);
        $openid = $data['openid'];

        $this->redirectURL($state,$openid);
    }


	//根据用户链接跳转
	public function  redirectURL($m = 'near',$openid){

		//数据库中是否已经有这个openid
		$DriverInfo = M('DriverInfo');
		$map = array();
		$map['openid'] = $openid;
		$driverData = $DriverInfo->where($map)->find();
		$tmpStr = '';
		if(empty($driverData)){
			$tmpStr = '&type=2';//还没有这个openid
		}
		else{
			$uid = $driverData['id'];
			$uuid = $this->createUUID($uid);
			$tmpStr = '&type=3&uid='.$uid.'&uuid='.$uuid;//已经有这个openid,返回uid和uuid
		}

        $baseURL = "http://static.duduche.me/redirect/user/indexhtml.php";
		$nearURL = $baseURL."?m=map&openid=".$openid.$tmpStr;
		$findURL =  $baseURL."?m=mapsearch&openid=".$openid.$tmpStr;
		$feeURL  =  $baseURL."?m=myjiesuan&openid=".$openid.$tmpStr;
        $orderURL  =  $baseURL."?m=myorder&openid=".$openid.$tmpStr;
        $userinfoURL  =  $baseURL."?m=userinfo&openid=".$openid.$tmpStr;
        $couponURL  =  $baseURL."?m=coupon&openid=".$openid.$tmpStr;


		switch($m){
			case 'near' : header("Location:".$nearURL); break;
			case 'find' : header("Location:".$findURL); break;
			case 'fee' : header("Location:".$feeURL); break;
            case 'order' : header("Location:".$orderURL); break;
            case 'userinfo' : header("Location:".$userinfoURL); break;
            case 'coupon' : header("Location:".$couponURL); break;
			default : header("Location:".$nearURL); break;
		}

        $this->_exit();
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

}
