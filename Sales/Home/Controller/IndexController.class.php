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
            $lat = I('get.lat');
            $lng = I('get.lng');
            $my = I('get.my');

            //查询停车场

            //只显示我自己的停车场，否则显示所有的停车场
            if(!empty($my)){
                $con['responsible'] = UID;
            }
            //附近停车场
            if(!empty($lat) && !empty($lng)){
                $near = C('NEAR_DIS');
                $gap = round($near/110000,6);
                $con['lat'] = array(array('gt',$lat - $gap),array('lt',$lat + $gap));
                $con['lng'] = array(array('gt',$lng - $gap),array('lt',$lng + $gap));
            }
            //根据名称和地址搜索
            if(!empty($searchName)){
                $where = array();
                $where['name']  = array('like','%'.$searchName.'%');
                $where['address']  = array('like','%'.$searchName.'%');
                $where['_logic'] = 'or';
                $con['_complex'] = $where;
            }
            $parks = $Park->where($con)->order('updatetime desc')->select();
            $this->parks_info = $parks;
        	$this->meta_title = '首页 | 嘟嘟销售系统';
        	$this->display();
        }
        else{
        	
        }
    }

    public function partime($id){
        $ParkInfo = M('ParkInfo');
        $map = array();
        $map['responsible'] = $id;
        $parkList = $ParkInfo->where($map)->select();
        $this->parks_info = $parkList;
        $this->meta_title = '首页 | 嘟嘟销售系统';
        $this->display();
    }

    public function parkinfo($parkid = null, $fileError = null){
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
            $parkInfo['prepay'] = I('post.prepay');
            $parkInfo['pretype'] = I('post.pretype');
 			$styles = I('post.parkstyle');
 			$parkstyle = "|";
 			foreach ($styles as $key => $value) {
 				$parkstyle = $parkstyle.$value.'|';
 			}
 			$parkInfo['style'] = $parkstyle;
 			$parkInfo['opentime'] = I('post.opentime');
 			$parkInfo['startmon'] = I('post.startwork');
 			$parkInfo['starttue'] = I('post.startwork');
 			$parkInfo['startwed'] = I('post.startwork');
 			$parkInfo['startthu'] = I('post.startwork');
 			$parkInfo['startfri'] = I('post.startwork');
 			$parkInfo['startsat'] = I('post.startweek');
 			$parkInfo['startsun'] = I('post.startweek');
 			$parkInfo['endmon'] = I('post.endwork');
 			$parkInfo['endtue'] = I('post.endwork');
 			$parkInfo['endwed'] = I('post.endwork');
 			$parkInfo['endthu'] = I('post.endwork');
 			$parkInfo['endfri'] = I('post.endwork');
 			$parkInfo['endsat'] = I('post.endweek');
 			$parkInfo['endsun'] = I('post.endweek');

            $freestartweek = I('post.freestartweek');
            $freeendweek = I('post.freeendweek');
            $fullstartweek = I('post.fullstartweek');
            $fullendweek = I('post.fullendweek');
            $freestartwork = I('post.freestartwork');
            $freeendwork = I('post.freeendwork');
            $fullstartwork = I('post.fullstartwork');
            $fullendwork = I('post.fullendwork');
            $parkInfo['freestartweek'] = empty($freestartweek) ? null : $freestartweek;
            $parkInfo['freeendweek'] = empty($freeendweek) ? null : $freeendweek;
            $parkInfo['fullstartweek'] = empty($fullstartweek) ? null : $fullstartweek;
            $parkInfo['fullendweek'] = empty($fullendweek) ? null : $fullendweek;
            $parkInfo['freestartwork'] = empty($freestartwork) ? null : $freestartwork;
            $parkInfo['freeendwork'] = empty($freeendwork) ? null : $freeendwork;
            $parkInfo['fullstartwork'] = empty($fullstartwork) ? null : $fullstartwork;
            $parkInfo['fullendwork'] = empty($fullendwork) ? null : $fullendwork;

 			$parkInfo['chargingrules'] = htmlspecialchars_decode(I('chargingrules'));
 			$parkInfo['note'] = I('note');
 			$parkInfo['shortname'] = I('shortname');

            //采用FTP方式，上传图片
            if($_FILES["parkimage"]["error"] == 0){//存在上传文件
                //上传图片的配置
                $config = array(
                    'maxSize'    =>    3145728,
                    'rootPath'   =>   C('PARK_UPLOAD_PATH'),
                    'savePath'   =>    '',
                    'saveName'   =>     'Park_'.I('post.id')."_".time(),
                    'exts'       =>    array('jpg', 'gif', 'png', 'jpeg'),
                    'autoSub'    =>    false,
                    'replace'      =>    true,
                );
                $upload = new \Think\Upload($config,'Ftp', C('UPLOAD_FTP'));// 实例化上传类
                $info   =   $upload->upload();
                if(!$info) {//上传错误
                    $fileError = $upload->getError();
                }
                else {//上传成功
                    //图片缩写的先去除
                    //$image = new \Think\Image();
                    //$imgURL = C('PARK_IMG_PATH').$info['parkimage']['savename'];
                    //$image->open($imgURL);
                    // 按照原图的比例生成一个最大为150*150的缩略图并保存为thumb.jpg
                    //$image->thumb(640, 300)->save($imgURL);
                    $parkInfo['image'] = $info['parkimage']['savename'];
                }
            }

            $e_t = I('post.e_t');
            $e_t_v = 0;
            foreach ($e_t as $k => $v) {
                $e_t_v = $e_t_v + pow(2,$v-1);
            }
            $parkInfo['e_t'] = $e_t_v;
            $parkInfo['e_p'] = I('post.e_p');
            $parkInfo['e_start'] = I('post.e_start');
            $parkInfo['e_end'] = I('post.e_end');


            //停车场活动参数
            $actype = I('post.actype');
            $acendtime = I('post.acendtime');
            $acscore = I('post.acscore');
            $parkInfo['actype'] = empty($actype) ?  null : $actype;
            $parkInfo['acendtime'] = empty($acendtime) ?  null : $acendtime;
            $parkInfo['acscore'] = empty($acscore) ? null : $acscore;

            $responsible = I('post.responsible');
            $parkInfo['responsible'] = empty($responsible) ? UID : $responsible;

    		$Park = D('ParkInfo');
            if( UID==100 && UID!=$parkInfo['responsible']){
                $saveParkId = 0;
            }
            else{
                $saveParkId = $Park->SaveParkInfo($parkInfo);
            }
    		if ($saveParkId) {
                $param = array('parkid' => $saveParkId);
                if(isset($fileError)){
                    $param['fileError'] = $fileError;
                }
    			$this->redirect('Index/parkinfo', $param, 0, '保存成功...');
    		}
    		else{
    			$this->error('抱歉，您不能修改其他人的负责的停车场！');
    		}

    	}
    	else {
    		$Park = D('ParkInfo');
    		$onePark = $Park->onePark($parkid);
    		$this->park_info = $onePark;
    		$this->meta_title = '停车场 | 嘟嘟销售系统';
            $this->fileError = $fileError;
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


    //获取下一个停车场，根据最新的更改时间
    public function nextpark($parkid){
        $ParkInfo = M('ParkInfo');
        $map = array();
        $map['creater'] = UID;
        $parks = $ParkInfo->where($map)->order('updatetime desc')->select();


        $this->redirect('/Home/Index/parkinfo/parkid/'.$parkid.'/');
    }



	//保存合作状态
	public function savecorp(){
		$parkid = I('post.id');
		$status = I('post.status');
        $infostatus = I('post.infostatus');

		//更新合作状态
		$Park = D('ParkInfo');
		$Park->status = $status + $infostatus;
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
        $emptyspace = I('post.emptyspace');

		$Visit = D('VisitRecord');
		$data['parkid'] = $parkid;
		$data['visitime'] = $visitime;
		$data['note'] = $note;
		$data['intention'] = $intention;
        $data['emptyspace'] = $emptyspace;

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
        $data['nickname'] = I('post.nickname');
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

    //修改个人基本信息
    public  function modifyInfo(){
        if (IS_POST) {
            $email = I('post.email');
            $telephone = I('post.telephone');
            $pwd1 = I('post.pwd1');
            $pwd2 = I('post.pwd2');

            $SalesAuth = M('SalesAuth');

            if($pwd1 === $pwd2){
                $map = array();
                $map['id'] = UID;
                $data = array();
                $data['email'] = $email;
                $data['telephone'] = $telephone;
                $data['updater'] = UID;
                $data['updatetime'] = date('Y-m-d H:i:s');

                if(!empty($pwd1)){
                    $data['password'] = strtoupper(md5($pwd1));
                }
                $SalesAuth->where($map)->save($data);
            }
            else{
                $this->msg = " * 两次输入的密码不一致，请重新输入！";
            }


            $map = array();
            $map['id'] = UID;
            $saleInfo = $SalesAuth->where($map)->find();
            $this->saleInfo = $saleInfo;
            $this->meta_title = '修改信息 | 嘟嘟销售管理系统';
            $this->display();


        }
        else{
            $SalesAuth = M('SalesAuth');
            $map = array();
            $map['id'] = UID;
            $saleInfo = $SalesAuth->where($map)->find();

            $this->saleInfo = $saleInfo;
            $this->meta_title = '修改信息 | 嘟嘟销售管理系统';
            $this->display();
        }

    }

    //修改个人基本信息
    public function checkShortName($shortName){
        $ParkInfo = M('ParkInfo');
        $map = array();
        $map['shortname'] = $shortName;
        $result = $ParkInfo->where($map)->find();
        if(empty($result)){
            echo  json_encode(array('check' => true));
        }
        else{
            echo  json_encode(array('check' => false));
        }

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