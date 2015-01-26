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

    		$Park = D('ParkInfo');
    		$saveParkId = $Park->SaveParkInfo($parkInfo);
    		if ($saveParkId) {
    			$this->redirect('/Home/Index/parkinfo/parkid/'.$saveParkId.'/#panel-1');
    		}
    		else{
    			$this->error();
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


	//保存拜访记录
	public function savevisit(){
		$parkid = I('post.id');
		$status = I('post.status');
		$contactInfo = array('contactname' => I('contactname'), 'contactgender' => I('contactgender'),'contactphone' => I('contactphone'),
			'contactjob' => I('contactjob'));

		$visitRecord = array('visitime' => I('visitime'), 'note' => I('note'), 'intention' => I('intention'));

		//更新合作状态
		$Park = D('ParkInfo');
		$Park->status = $status;
		$Park->where('id = '.$parkid)->save();

		//更新联系人数据
		$Contact = D('ContactInfo');
		//先把数据全部删除
		$map = array();
		$map['parkid'] = $parkid;
		$Contact->where($map)->delete();
		//再添加新数据
		$count = count($contactInfo['contactname']);
		$dataList1 = array();
		for ($i=0; $i < $count ; $i++) {
			$dataList1[] = array('parkid' => $parkid,'name' => $contactInfo['contactname'][$i], 'gender' => $contactInfo['contactgender'][$i],
				'telephone' => $contactInfo['contactphone'][$i], 'job' => $contactInfo['contactjob'][$i], 'creater' => UID,
				'createtime' => date('Y-m-d H:i:s'),'updater' => UID);
		}
		$result1 = $Contact->addAll($dataList1);


		//更新拜访记录
		$Visit = D('VisitRecord');
		//先把数据全部删除
		$map = array();
		$map['parkid'] = $parkid;
		$Visit->where($map)->delete();
		//再添加新数据
		$count = count($visitRecord['visitime']);
		$dataList2 = array();
		for ($i=0; $i < $count ; $i++) {
			$dataList2[] = array('parkid' => $parkid,'visitime' => $visitRecord['visitime'][$i], 'intention' => $visitRecord['intention'][$i],
				'note' => $visitRecord['note'][$i], 'creater' => UID, 'createtime' => date('Y-m-d H:i:s'),'updater' => UID);
		}
		$result2 = $Visit->addAll($dataList2);


		if($result1 && $result2){
			$this->redirect('/Home/Index/parkinfo/parkid/'.$parkid.'/#panel-2');
		}
		else{
			$this->error();
		}

	}

}