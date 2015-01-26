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
    		$this->feeurl = U('Home/Index/parkfee',array('parkid'=>$parkid,'parkname'=>$onePark['name'],'rules'=>$onePark['chargingrules']));
    		$rulestime = M('rules_time');
    		$con1 = "parkid=".$parkid;
				$this->rulecount = $rulestime->where($con1)->count();
					
    		$this->display();
    	}
    }
    
    public function parkfee($parkid = null,$parkname = null,$rules = null){
    	$this->formurl=U('parkfee',array('parkid'=>$parkid,'parkname'=>$parkname,'rules'=>$rules));
    	$rulestime = M('rules_time');
    	$rulesmoney = M('rules_money');
    	
    	if (IS_POST) {
    		$ruleid = I('post.ruleid');
    		$ruleop = I('post.ruleop');
    		if($ruleid > 0){
    			if($ruleop == ''){//del rule
    				$con1 = "id=".$ruleid;
    				$rulestime->where($con1)->delete();
    				$con2 = "rulesid=".$ruleid;
    				$rulesmoney->where($con2)->delete();
    			}else{//modify rule
    				$rulesArr = explode(';',$ruleop);
	    			$rulesCount = count($rulesArr);
	    			if($rulesCount < 3){
	    				$this->error("停车规则参数不足，无法保存！");
	    				return;
	    			}
	    			$starttime = $rulesArr[0];
	    			$endtime = $rulesArr[1];
	    			$ruledata = array('startime'=>$starttime,'endtime'=>$endtime);
	    			$rulestime->where("id=".$ruleid)->save($ruledata);
	    			$rulesmoney->where("rulesid=".$ruleid)->delete();
	    			for($i=2;$i<$rulesCount;$i++){//保存费用信息
    					$feeArr=explode(',',$rulesArr[$i]);
    					$feedata = array('rulesid'=>$ruleid,'mins'=>$feeArr[0],'money'=>$feeArr[1],'createtime'=>time());
    					$rulesmoney->add($feedata);
    				}
    			}
    		}else if($ruleop != ''){//add rule
    			$rulesArr = explode(';',$ruleop);
    			$rulesCount = count($rulesArr);
    			if($rulesCount < 3){
    				$this->error("停车规则参数不足，无法保存！");
    				return;
    			}
    			$starttime = $rulesArr[0];
    			$endtime = $rulesArr[1];
    			$ruledata = array('parkid'=>$parkid,'startime'=>$starttime,'endtime'=>$endtime,'createtime'=>time());
    			$ruleid = $rulestime->add($ruledata);//保存规则
    			if($ruleid){
    				for($i=2;$i<$rulesCount;$i++){//保存费用信息
    					$feeArr=explode(',',$rulesArr[$i]);
    					$feedata = array('rulesid'=>$ruleid,'mins'=>$feeArr[0],'money'=>$feeArr[1],'createtime'=>time());
    					$rulesmoney->add($feedata);
    				}
    			}else{
    				$this->error($error);
    			}
    		}
    	}
    	
    		$con1 = "parkid=".$parkid;
				$this->rulesdata = $rulestime->where($con1)->order('startime')->select();
				$this->rulesmoney = $rulesmoney;
			
	    	$this->meta_title = '计费规则库 | 嘟嘟销售系统';
	    	$this->parkid=$parkid;
	    	$this->parkname=$parkname;
	    	$this->rules=$rules;
	    	$this->display();
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