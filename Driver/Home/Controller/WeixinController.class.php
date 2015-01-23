<?php
use Think\Controller;
class WeixinController extends Controller {
    public function index(){
 		echo $this->checkSignature();
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
		sort($tmpArr);
		$tmpStr = implode( $tmpArr );
		$tmpStr = sha1( $tmpStr );

		if( $tmpStr == $signature ){
			if($_GET["echostr"]) {
				echo $_GET["echostr"];
				exit(0);
			}
		}else{
			//恶意请求：获取来来源ip，并写日志
			echo "error!";
			exit(0);	
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
