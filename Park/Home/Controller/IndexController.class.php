<?php
namespace Home\Controller;
use Think\Controller;
class IndexController extends Controller {
    public function index(){
        $this->show("Park!");
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