<?php
/**
 * Created by PhpStorm.
 * User: Bin
 * Date: 15/3/4
 * Time: 下午4:31
 */

class PublicController extends BaseController {

    /**
     * 后台用户登录
     */
    public function login($username = null, $password = null, $verify = null){
        if(IS_POST){
            // 检测验证码
            if(!$this->check_verify($verify)){
                //$this->error('验证码输入错误！');
            }

            //验证用户名、密码
            $map = array();
            $map['username'] = $username;
            /* 获取用户数据 */
            $Admin = M('AdminAuth');
            $Member = $Admin->where($map)->find();

            if(is_array($Member)){
                /* 验证用户密码 */
                if( $Member['password'] === strtoupper(md5($password))) {
                    //登录成功
                    $uid = $Member['id'];
                    $auth = array(
                        'uid'             => $Member['id'],
                        'username'        => $Member['username'],
                    );

                    //记录行为日志
                    //action_log('user_login', 'member', $uid, $uid);

                    // session记录登录信息
                    session('admin_auth', $auth);

                    $this->success('登录成功！', U('Index/index'));

                } else {
                    $this->error('密码错误');
                }
            } else {
                $this->error('用户不存在或被禁用');
            }
        } else {
            if($this->is_login()){
                $this->redirect('Index/index');
            }else{
                $this->display();
            }
        }
    }

    //退出登录 ,清除 session
    public function logout(){
        if($this->is_login()){
            session('admin_auth', null);
            session('[destroy]');
            $this->redirect('login');
        } else {
            $this->redirect('login');
        }
    }

    //生成 验证码
    public function verify(){
        $verify = new \Think\Verify();
        $verify->entry(1);
    }
    
    public function test_park122($city='sh'){
    	include_once(dirname(__FILE__) . '/../Conf/' . 'config_mappoints.php');
    	$jsondata = null;
    	$dbname = null;
    	$url = null;
    	if($city=='gz'){
    		$jsondata = $map122_gz;
    		$dbname = 'test_122park_gz';
    		$qurl = 'http://www.122park.com/gz/mapquery.php?sidinfo=';
    	}else if($city=='sz'){
    		$jsondata = $map122_sz;
    		$dbname = 'test_122park_sz';
    		$qurl = 'http://www.122park.com/sz/mapquery.php?sidinfo=';
    	}else{
    		$jsondata = $map122_sh;
    		$dbname = 'test_122park_sh';
    		$qurl = 'http://www.122park.com/sh/mapquery.php?sidinfo=';
    	}
    	
    	$data = json_decode($jsondata, TRUE);
    	//var_dump($data);
    	//$DOM = new DOMDocument;
    	$db = M($dbname);
     	foreach($data as $value){
     		//$value = $data[0];
    		$json = trim(file_get_contents($qurl.$value['sid']));
    		//var_dump($json);
    		$item = json_decode(mb_convert_encoding($json,'UTF-8','GBK'),TRUE);
    		//var_dump($item);
    		/*$DOM->loadHTML($item['title']);
    		$title = $this->dom_to_str($DOM);
    		$DOM->loadHTML($item['content']);
    		$content = $this->dom_to_str($DOM);*/
    		$title = $this->getTitle($item['title']);
    		$content = $this->getContent($item['content']);
    		
    		$dbdata = array('sid'=>$value['sid'],'lat'=>$value['map_lat'],'lng'=>$value['map_lng'],'title'=>$title, 'price'=>$content['price'], 'dsc'=>$content['desc']);
				var_dump($dbdata);
				$db->add($dbdata);
    	}
    	
    }
    
    protected function getTitle($title){
    	$mark1 = "<strong class='infoWindowTitle'>";
    	$pos1 = strpos($title, $mark1);
    	//echo $pos1."<br>";
    	$mark2 = "</strong>";
    	$pos2 = strpos($title, $mark2, $pos1);
    	//echo $pos2."<br>";
    	return substr($title, $pos1+strlen($mark1), $pos2 - $pos1 - strlen($mark1));
    }
    
    protected function getContent($content){
    	$mark1 = "</strong>";
    	$pos1 = strpos($content, $mark1);
    	//echo $pos1."<br>";
    	$mark2 = "<p>";
    	$pos2 = strpos($content, $mark2, $pos1);
    	//echo $pos2."<br>";
    	$price = substr($content, $pos1+strlen($mark1), $pos2 - $pos1 - strlen($mark1));
    	$mark1 = "</strong>";
    	$pos1 = strpos($content, $mark1, $pos2);
    	//echo $pos1."<br>";
    	$mark2 = "</p>";
    	$pos2 = strpos($content, $mark2, $pos1);
    	//echo $pos2."<br>";
    	$desc = substr($content, $pos1+strlen($mark1), $pos2 - $pos1 - strlen($mark1));
    	
    	return array('price'=>$price,'desc'=>$desc);
    	
    }
    
protected function dom_to_str($root) 
{ 
    $result = ''; 

    $children = $root->childNodes; 

    if ($children->length == 1) 
    { 
        $child = $children->item(0); 

        if ($child->nodeType == XML_TEXT_NODE) 
        { 
            $result = $child->nodeValue; 

            return $result; 
        } 
    }

    for($i = 0; $i < $children->length; $i++) 
    { 
        $child = $children->item($i); 

        $result .= $this->dom_to_str($child); 
    } 

    return $result; 
} 

}
