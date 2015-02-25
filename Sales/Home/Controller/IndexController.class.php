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
			$parkInfo['address2'] = I('post.address2');
			$parkInfo['lat'] = I('lat');
			$parkInfo['lng'] = I('lng');
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
    		$this->feeurl = U('Home/Index/parkfee',array('parkid'=>$parkid));
			$rulestime = M('rules_time');
			$cond = array();
			$cond['parkid'] = $parkid;
			$this->rulecount = $rulestime->where($cond)->count();
    		$this->display();
    	}
    }
    
    public function parkfee($parkid = null){
    	$this->formurl=U('parkfee',array('parkid'=>$parkid));
    	$rulestime = M('rules_time');
    	$rulesmoney = M('rules_money');
    	
    	if (IS_POST) {
    		$ruleid = I('post.ruleid');
    		$ruleop = I('post.ruleop');
    		if($ruleid > 0){
    			if($ruleop == ''){//del rule

					$con1 = array();
					$con1['id'] = $ruleid;
    				$rulestime->where($con1)->delete();
					$con2 = array();
					$con2['rulesid'] = $ruleid;
    				$rulesmoney->where($con2)->delete();
    			}else{//modify rule
    				$rulesArr = explode(';',$ruleop);
	    			$rulesCount = count($rulesArr);
	    			if($rulesCount < 5){
	    				$this->error("停车规则参数不足，无法保存！");
	    				return;
	    			}
	    			$starttime = $rulesArr[0];
	    			$endtime = $rulesArr[1];
	    			$stopatend = $rulesArr[2];
	    			$stoptime = $rulesArr[3];
	    			$ruledata = array('startime'=>$starttime,'endtime'=>$endtime,'stopatend'=>$stopatend,'stoptime'=>$stoptime);
					$con3 = array();
					$con3['id'] = $ruleid;
	    			$rulestime->where($con3)->save($ruledata);
					$con4 = array();
					$con4['rulesid'] = $ruleid;
	    			$rulesmoney->where($con4)->delete();
	    			for($i=4;$i<$rulesCount;$i++){//保存费用信息
    					$feeArr=explode(',',$rulesArr[$i]);
    					$feedata = array('rulesid'=>$ruleid,'mins'=>$feeArr[0],'money'=>$feeArr[1],'createtime'=>time());
    					$rulesmoney->add($feedata);
    				}
    			}
    		}else if($ruleop != ''){//add rule
    			$rulesArr = explode(';',$ruleop);
    			$rulesCount = count($rulesArr);
    			if($rulesCount < 5){
    				$this->error("停车规则参数不足，无法保存！");
    				return;
    			}
    			$starttime = $rulesArr[0];
    			$endtime = $rulesArr[1];
    			$stopatend = $rulesArr[2];
    			$stoptime = $rulesArr[3];
    			$ruledata = array('parkid'=>$parkid,'startime'=>$starttime,'endtime'=>$endtime,'stopatend'=>$stopatend,'stoptime'=>$stoptime,'createtime'=>time());
    			$ruleid = $rulestime->add($ruledata);//保存规则
    			if($ruleid){
    				for($i=4;$i<$rulesCount;$i++){//保存费用信息
    					$feeArr=explode(',',$rulesArr[$i]);
    					$feedata = array('rulesid'=>$ruleid,'mins'=>$feeArr[0],'money'=>$feeArr[1],'createtime'=>time());
    					$rulesmoney->add($feedata);
    				}
    			}else{
    				$this->error($error);
    			}
    		}
    	}
			$con5 = array();
			$con5['parkid'] = $parkid;
			$this->rulesdata = $rulestime->where($con5)->order('startime')->select();
			$this->rulesmoney = $rulesmoney;
			
	    	$this->meta_title = '计费规则库 | 嘟嘟销售系统';
	    	$this->parkid=$parkid;

			$Park = D('ParkInfo');
			$con6 = array();
			$con6['id'] = $parkid;
			$parkData = $Park->where($con6)->find();

	    	$this->parkname=$parkData['name'];
	    	$this->rules=$parkData['chargingrules'];
	    	$this->display();
    }



	//保存合作状态
	public function savecorp(){
		$parkid = I('post.id');
		$status = I('post.status');
		//更新合作状态
		$Park = D('ParkInfo');
		$Park->status = $status;
		$Park->updater = UID;
		$Park->updatetime = date('Y-m-d H:i:s');
		$con = array();
		$con['id'] = $parkid;
		$Park->where($con)->save();

		$this->redirect('/Home/Index/parkinfo/parkid/'.$parkid.'/#panel-2');
	}


	//保存每条拜访记录
	public function savevisit(){

		$parkid = I('post.parkid');
		$id = I('post.id');
		$visitime = I('post.visitime');
		$note = I('post.note');
		$intention = I('post.intention');

		$Visit = D('VisitRecord');
		$data['parkid'] = $parkid;
		$data['visitime'] = $visitime;
		$data['note'] = $note;
		$data['intention'] = $intention;

		if($id  == ''){//新建
			$data['creater'] = UID;
			$data['createtime'] = date('Y-m-d H:i:s');
			$data['updater'] = UID;

			$Visit->add($data);
		}
		else{//更新
			$data['updater'] = UID;

			$map = array();
			$map['id'] = $id;

			$Visit->where($map)->save($data);
		}

		//更新主表的更新日期，表示其有过修改
		$Park = D('ParkInfo');
		$Park->updatetime = date('Y-m-d H:i:s');
		$con = array();
		$con['id'] = $parkid;
		$Park->where($con)->save();

		$this->redirect('/Home/Index/parkinfo/parkid/'.$parkid.'/#panel-2');
	}

	//保存拜访记录
	public function delvisit(){
		$parkid = I('post.parkid');
		$id = I('post.id');

		$Visit = D('VisitRecord');
		$map = array();
		$map['id'] = $id;
		$Visit->where($map)->limit('1')->delete();

		//更新主表的更新日期，表示其有过修改
		$Park = D('ParkInfo');
		$Park->updatetime = date('Y-m-d H:i:s');
		$con = array();
		$con['id'] = $parkid;
		$Park->where($con)->save();

		$this->redirect('/Home/Index/parkinfo/parkid/'.$parkid.'/#panel-2');
	}

	//保存每条联系人
	public function savecontact(){
		$parkid = I('post.parkid');
		$id = I('post.id');
		$contactname = I('post.contactname');
		$contactgender = I('post.contactgender');
		$contactphone = I('post.contactphone');
		$contactjob = I('post.contactjob');

		$Contact = D('ContactInfo');
		$data['parkid'] = $parkid;
		$data['name'] = $contactname;
		$data['gender'] = $contactgender;
		$data['telephone'] = $contactphone;
		$data['job'] = $contactjob;

		if($id  == ''){//新建
			$data['creater'] = UID;
			$data['createtime'] = date('Y-m-d H:i:s');
			$data['updater'] = UID;

			$Contact->add($data);
		}
		else{//更新
			$data['updater'] = UID;

			$map = array();
			$map['id'] = $id;

			$Contact->where($map)->save($data);
		}

		//更新主表的更新日期，表示其有过修改
		$Park = D('ParkInfo');
		$Park->updatetime = date('Y-m-d H:i:s');
		$con = array();
		$con['id'] = $parkid;
		$Park->where($con)->save();

		$this->redirect('/Home/Index/parkinfo/parkid/'.$parkid.'/#panel-2');
	}


	//删除联系人
	public function delcontact(){
		$parkid = I('post.parkid');
		$id = I('post.id');

		$Contact = D('ContactInfo');
		$map = array();
		$map['id'] = $id;
		$Contact->where($map)->limit('1')->delete();

		//更新主表的更新日期，表示其有过修改
		$Park = D('ParkInfo');
		$Park->updatetime = date('Y-m-d H:i:s');
		$con = array();
		$con['id'] = $parkid;
		$Park->where($con)->save();

		$this->redirect('/Home/Index/parkinfo/parkid/'.$parkid.'/#panel-2');
	}

	//保存车场管理员
	public function saveadmin(){
		$parkid = I('post.parkid');
		$id = I('post.id');

		$Admin = D('ParkAdmin');
		$data['parkid'] = $parkid;
		$data['parkname'] = I('post.parkname');
		$data['username'] = I('post.username');
		if(I('post.password') != ''){
			$data['password'] = strtoupper(md5(I('post.password')));
		}
		$data['name'] = I('post.name');
		$data['phone'] = I('post.phone');

		$jobs= I('post.jobfunction');
		$jobfun = 0;
		foreach ($jobs as $key => $value) {
			$jobfun += intval($value);
		}
		$data['jobfunction'] = $jobfun;

		if($id  == ''){//新建
			$data['creater'] = UID;
			$data['createtime'] = date('Y-m-d H:i:s');
			$data['updater'] = UID;

			$Admin->add($data);
		}
		else{//更新
			$data['updater'] = UID;

			$map = array();
			$map['id'] = $id;

			$Admin->where($map)->save($data);
		}


		//更新主表的更新日期，表示其有过修改
		$Park = D('ParkInfo');
		$Park->updatetime = date('Y-m-d H:i:s');
		$con = array();
		$con['id'] = $parkid;
		$Park->where($con)->save();

		$this->redirect('/Home/Index/parkinfo/parkid/'.$parkid.'/#panel-2');
	}


	//删除车场管理员
	public function deladmin(){
		$parkid = I('post.parkid');
		$id = I('post.id');

		$Admin = D('ParkAdmin');
		$map = array();
		$map['id'] = $id;
		$Admin->where($map)->limit('1')->delete();

		//更新主表的更新日期，表示其有过修改
		$Park = D('ParkInfo');
		$Park->updatetime = date('Y-m-d H:i:s');
		$con = array();
		$con['id'] = $parkid;
		$Park->where($con)->save();

		$this->redirect('/Home/Index/parkinfo/parkid/'.$parkid.'/#panel-2');
	}

//	//保存所以拜访记录，早期的，不能记录每条记录的改变。
//	public function savevisit1(){
//		$parkid = I('post.id');
//		$status = I('post.status');
//		$contactInfo = array('contactname' => I('post.contactname'), 'contactgender' => I('post.contactgender'),'contactphone' => I('post.contactphone'),
//			'contactjob' => I('post.contactjob'));
//
//		$visitRecord = array('visitime' => I('post.visitime'), 'note' => I('post.note'), 'intention' => I('post.intention'));
//
//		//更新合作状态
//		$Park = D('ParkInfo');
//		$Park->status = $status;
//		$Park->updater = UID;
//		$Park->updatetime = date('Y-m-d H:i:s');
//		$con = array();
//		$con['id'] = $parkid;
//		$Park->where($con)->save();
//
//		//更新联系人数据
//		$Contact = D('ContactInfo');
//		//先把数据全部删除
//		$map = array();
//		$map['parkid'] = $parkid;
//		$Contact->where($map)->delete();
//		//再添加新数据
//		if(is_array($contactInfo['contactname'])){//判断是否有传入的值
//			$count = count($contactInfo['contactname']);
//			$dataList1 = array();
//			for ($i=0; $i < $count ; $i++) {
//				$dataList1[] = array('parkid' => $parkid,'name' => $contactInfo['contactname'][$i], 'gender' => $contactInfo['contactgender'][$i],
//					'telephone' => $contactInfo['contactphone'][$i], 'job' => $contactInfo['contactjob'][$i], 'creater' => UID,
//					'createtime' => date('Y-m-d H:i:s'),'updater' => UID);
//			}
//			$Contact->addAll($dataList1);
//		}
//
//
//		//更新拜访记录
//		$Visit = D('VisitRecord');
//		//先把数据全部删除
//		$map = array();
//		$map['parkid'] = $parkid;
//		$Visit->where($map)->delete();
//		//再添加新数据
//		if(is_array($visitRecord['visitime'])){
//			$count = count($visitRecord['visitime']);
//			$dataList2 = array();
//			for ($i=0; $i < $count ; $i++) {
//				$dataList2[] = array('parkid' => $parkid,'visitime' => $visitRecord['visitime'][$i], 'intention' => $visitRecord['intention'][$i],
//					'note' => $visitRecord['note'][$i], 'creater' => UID, 'createtime' => date('Y-m-d H:i:s'),'updater' => UID);
//			}
//			$Visit->addAll($dataList2);
//		}
//		$this->redirect('/Home/Index/parkinfo/parkid/'.$parkid.'/#panel-2');
//
//	}

}