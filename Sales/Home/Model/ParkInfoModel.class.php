<?php
 
use Think\Model;

/**
 * 销售人员模型
 */

class ParkInfoModel extends Model {

    /**
     * 根据名字查询停车场信息
     * @param  string  $parkid 停车场名字
     */
    public function searchPark($parkname){
        $SalesAuth= M('SalesAuth');
        $map = array();
        $map['leader'] = UID;//销售主管能看到所有
        $sales = $SalesAuth->where($map)->getField('id',true);
        if(empty($sales)){
            $sales = array();
            array_push($sales, UID);
        }
        else{
            array_push($sales, UID);
        }


        $map = array(); 
        $map['name'] = array('like','%'.$parkname.'%');
        $map['responsible'] = array('in',$sales);
        /* 获取数据 */
        $Park = $this->where($map)->order('updatetime desc')->select();

        if(is_array($Park)){
            return $Park;
        } else {
            return NULL; //停车场不存在或被禁用
        }
    }

	/**
	 * 根据id查询停车场信息
	 * @param  string  $parkid 停车场id
	 */
	public function onePark($parkid){
        $map = array(); 
        $map['id'] = $parkid;     
		/* 获取用户数据 */
		$Park = $this->where($map)->find();

        $ContactInfo = D('ContactInfo');
        $Contact = $ContactInfo->findContacts($parkid);
        $Park["Contact"] = $Contact;

        $VisitRecord = D('VisitRecord');
        $con = array();
        $con['parkid'] = $parkid;
        $visitData = $VisitRecord->where($con)->order('updatetime desc')->select();
        $Park['Visit'] = $visitData;

        $ParkAdmin = D('ParkAdmin');
        $con = array();
        $con['parkid'] = $parkid;
        $adminData = $ParkAdmin->where($con)->select();
        $Park['Admin'] = $adminData;

		if(is_array($Park)){
            return $Park;
		} else {
			return NULL; //停车场不存在或被禁用
		}
	}

    /**
     * 保存停车场信息
     * @param  string  $parkid 停车场id
     */
    public function SaveParkInfo($parkInfo){
        $map = array(); 
        $map['id'] = $parkInfo['id'];     
        /* 获取停车场数据 */
        $Park = $this->where($map)->find();
        
        $result = NULL;
        if(is_array($Park)){
            /* 更新停车场数据 */
            $parkInfo['updater'] = UID;
            $parkInfo['updatetime'] = date('Y-m-d H:i:s');
            $this->save($parkInfo);
            $result = $parkInfo['id'];
        } else {
            /* 添加停车场数据 */
            $parkInfo['creater'] = UID;
            $parkInfo['createtime'] = date('Y-m-d H:i:s');
            $parkInfo['updater'] = UID;
            $parkInfo['updatetime'] = date('Y-m-d H:i:s');
            if($this->create($parkInfo)){
                $result = $this->add();
            } else {
                return $this->getError(); //错误详情见自动验证注释
            }
        }


       return $result;
    }

 
}
