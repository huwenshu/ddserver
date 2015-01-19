<?php

/**
 * 后台首页控制器
 * @Bin
 */

class IndexController extends  BaseController {
    public function index(){
    	if (IS_GET) {
        	$Park = D('ParkInfo');
        	$searchName = I('get.searchpark');
        	$parks = $Park->searchPark($searchName);
        	$this->parks_info = $parks;
        	$this->meta_title = '首页 | 嘟嘟销售系统';
        	$this->display();
        }
        else{
        	
        }
    }

    public function parkinfo($parkid = null){
    	if (IS_POST) {
    		$parkInfo = array();
    		//处理POST过来的信息
 			$parkInfo['id'] =  I('post.id');
 			$parkInfo['name'] = I('post.name');
 			$parkInfo['address'] = I('post.address');
 			$parkInfo['spacesum'] = I('post.spacesum');
 			$styles = I('post.parkstyle');
 			$parkstyle = "|";
 			foreach ($styles as $key => $value) {
 				$parkstyle = $parkstyle.$value.'|';
 			}
 			$parkInfo['style'] = $parkstyle;
 			$parkInfo['opentime'] = I('post.opentime');
 			$parkInfo['chargingrules'] = I('chargingrules');
 			$parkInfo['note'] = I('note');
 			$parkInfo['shortname'] = I('shortname');
 			$parkInfo['status'] = I('status');

 			$contactInfo = array('contactname' => I('contactname'), 'contactgender' => I('contactgender'),'contactphone' => I('contactphone'),
 				'contactjob' => I('contactjob'));

    		$Park = D('ParkInfo');
    		$saveParkId = $Park->SaveParkInfo($parkInfo, $contactInfo);
    		if ($saveParkId) {
    			$this->redirect('/Home/Index/parkinfo/parkid/'.$saveParkId.'/');
    		}
    		else{
    			$this->error($error);
    		}

    	}
    	else {
    		$Park = D('ParkInfo');
    		$onePark = $Park->onePark($parkid);
    		$this->park_info = $onePark;
    		$this->meta_title = '停车场 | 嘟嘟销售系统';
    		$this->display();
    	}
    }

}