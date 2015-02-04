<?php
use Think\Controller;
class WeixinController extends BaseController {
    public function index(){
		if($this->checkSignature()) {
			if($_GET["echostr"]) {
				echo $_GET["echostr"];
				exit(0);
			}

		} else {
			exit(0);
		}
		$postStr = file_get_contents ( "php://input" );
		if (!empty ( $postStr )) {
			$postObj = simplexml_load_string ( $postStr, 'SimpleXMLElement', LIBXML_NOCDATA );
			if(NULL == $postObj) {
				exit(0);
			}
			else{
				$this->process($postObj);
			}
		}
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
			
    }

    public function getToken(){
    	$para = array(
					"grant_type" => "client_credential",
					"appid" => C('APPID'),
					"secret" => C('APPSECRET')
				);
    	$url = C('WX_API_URL')."token";
    	$ret = $this->doCurlGetRequest($url, $para);
    	$retData = json_decode($ret, true);
    	$token = $retData['access_token'];
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

		//数据库中是否已经有这个openid
		$DriverInfo = M('DriverInfo');
		$map = array();
		$map['openid'] = $_openid;
		$driverData = $DriverInfo->where($map)->find();
		$tmpStr = '';
		if(empty($driverData)){
			$tmpStr = '&type=2';//还没有这个openid
		}
		else{
			$uid = $driverData['id'];
			$uuid = $this->createUUID($uid);
			$tmpStr = '&type=3&uid='.$uid.'$uuid='.$uuid;//已经有这个openid,返回uid和uuid
		}

		$nearURL = "http://duduche.me/html/userhtml/index.html?m=map&openid=".$_openid.$tmpStr;
		$findURL = "http://duduche.me/html/userhtml/index.html?m=mapsearch&openid=".$_openid.$tmpStr;
		$feeURL  = "http://duduche.me/html/userhtml/index.html?m=myjiesuan&openid=".$_openid.$tmpStr;

		if ($_msgType == 'event') {
			$_event = (string)$postObj->Event;
			$_eventKey = (string)$postObj->EventKey;
			if($_event == 'subscribe'){
				$content = '欢迎您关注嘟嘟停车！我们专注于解决您的停车难问题，目前尚只在上海提供服务，其他城市正在准备中。
嘟嘟停车有如下功能，请点击下面的链接使用：

1-找附近的空车位：<a href="'.$nearURL.'">单击这里</a>

2-搜索地址找空车位：<a href="'.$findURL.'">单击这里</a>

3-停车缴费：<a href="'.$feeURL.'">单击这里</a>';
				$resultStr = sprintf ( C('HINT_TPL'), $_openid, C('USERNAME_WEIXIN'), time(), 'text', $content );
				echo  $resultStr;
			}
		}
		else{
			$content = '欢迎您关注嘟嘟停车！我们专注于解决您的停车难问题，目前尚只在上海提供服务，其他城市正在准备中。
嘟嘟停车有如下功能，请点击下面的链接使用：

1-找附近的空车位：<a href="'.$nearURL.'">单击这里</a>

2-搜索地址找空车位：<a href="'.$findURL.'">单击这里</a>

3-停车缴费：<a href="'.$feeURL.'">单击这里</a>';
			$resultStr = sprintf ( C('HINT_TPL'), $_openid,  C('USERNAME_WEIXIN'), time(), 'text', $content );
			//Think\Log::write($resultStr,'ERR');
			echo  $resultStr;
		}

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
