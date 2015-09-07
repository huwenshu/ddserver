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

		$nearURL = "http://duduche.me/driver.php/home/weixin/redirectURL/m/map/openid/".$_openid;
        $disURL = "http://duduche.me/driver.php/home/weixin/redirectURL/m/discover/openid/".$_openid;
        $downURL  = "http://driver.duduche.me/driver.php/home/public/wx_app_down/";

		if ($_msgType == 'event') {
			$_event = (string)$postObj->Event;
			$_eventKey = (string)$postObj->EventKey;
			if($_event == 'subscribe'){
                //记录扫描，关注事件
                $WeixinEvent = M('WeixinEvent');
                $data = array();
                $data['fromusername'] = $_openid;
                $data['createtime'] = (int)trim($postObj->CreateTime);
                $data['msgtype'] = $_msgType;
                $data['event'] = $_event;
                $data['eventkey'] = substr($_eventKey,8);
                $data['ticket'] = (string)$postObj->Ticket;
                $WeixinEvent->add($data);

				$content = '欢迎您关注嘟嘟停车！我们专注于解决您的停车难问题。

第六医院首停只要1分钱。<a href="https://open.weixin.qq.com/connect/oauth2/authorize?appid=wxd417c2e70f817f89&redirect_uri=http%3a%2f%2fdriver.duduche.me%2fdriver.php%2fhome%2fweixin%2fmenuCallBack%2f&response_type=code&scope=snsapi_base&state=discover#wechat_redirect">立即预定</a>';

                $resultStr = sprintf ( C('HINT_TPL'), $_openid, C('USERNAME_WEIXIN'), time(), 'text', $content );
				echo  $resultStr;
			}
            elseif($_event == 'SCAN'){
                //记录扫描，关注事件
                //记录扫描，关注事件
                $WeixinEvent = M('WeixinEvent');
                $data = array();
                $data['fromusername'] = $_openid;
                $data['createtime'] = (int)trim($postObj->CreateTime);
                $data['msgtype'] = $_msgType;
                $data['event'] = $_event;
                $data['eventkey'] = $_eventKey;
                $data['ticket'] = (string)$postObj->Ticket;
                $WeixinEvent->add($data);
            }
		}
		else{
			$content = '谢谢您的反馈，我们会努力做的更好！';
			$resultStr = sprintf ( C('HINT_TPL'), $_openid,  C('USERNAME_WEIXIN'), time(), 'text', $content );
			//Think\Log::write($resultStr,'ERR');
			echo  $resultStr;
		}

	}

    //处理领取红包，获取openid
    public  function giftCallBack($code,$type=10,$hcode='',$fromid=0){
        //访问微信接口，获取openid
        $URL = 'https://api.weixin.qq.com/sns/oauth2/access_token?appid='.C('APPID').'&secret='.C('APPSECRET').'&code='.$code.'&grant_type=authorization_code';
        $data = json_decode($this->doCurlGetRequest($URL),true);
        $openid = $data['openid'];

        //加入dudu_openid_valid表，有限的openid，防止作弊！
        $OpenidValid = M('OpenidValid');
        $map = array();
        $map['openid'] = $openid;
        $result = $OpenidValid->where($map)->find();
        if(empty($result)){
            $data = array();
            $data['openid'] = $openid;
            $data['code'] = $hcode;
            $data['fromid'] = $fromid;
            $data['valid'] = 0;
            $data['createtime'] = date("Y-m-d H:i:s",time());
            $OpenidValid->add($data);
        }
        else{
            if($result['valid'] == 0){
                $data = array();
                $data['id'] = $result['id'];
                $data['code'] = $hcode;
                $data['fromid'] = $fromid;
                $OpenidValid->save($data);
            }
        }

        $url = 'http://static.duduche.me/redirect/user/hongbao.php?type='.$type.'&hcode='.$hcode.'&fromid='.$fromid.'&openid='.$openid;
        header("Location:".$url);

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
        $reURL = $baseURL."?m=".$m."&openid=".$openid.$tmpStr;
        header("Location:".$reURL);

        $this->_exit();
	}

}
