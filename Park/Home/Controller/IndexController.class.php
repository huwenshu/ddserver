<?php
namespace Home\Controller;
use Think\Controller;
class IndexController extends Controller {
    public function index(){
        $appid = "wx7402a94935807c76";
        $basere = "http://115.29.160.95:81/Park.php/Home/Index/getOpenid/";
        $basere = urlencode($basere);
        $baseurl = "http://open.weixin.qq.com/connect/oauth2/authorize?appid={$appid}&redirect_uri={$basere}&response_type=code&scope=snsapi_base&state=STATE#wechat_redirect";
        echo $baseurl;
    }

    public function getOpenid(){
        $code = $_GET["code"];
        $appid ="wx7402a94935807c76";
        $secret = "59023166c76dbbb5d82e81318d514893";
        $url = "https://api.weixin.qq.com/sns/oauth2/access_token?appid={$appid}&secret={$secret}&code={$code}&grant_type=authorization_code";
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        $a = curl_exec($ch);
        $strjson = json_decode($a);
        $openid = $strjson->openid;
        echo "openid:".openid;
        
    }
    public function test($username){
    	$result = array(
						'code'=>100,
						'data'=>'Hello,'.$username.'!'
				  );

    $this->ajaxReturn($result,'jsonp');
    exit;

    }
}