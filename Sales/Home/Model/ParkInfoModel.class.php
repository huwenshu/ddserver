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
        $map = array(); 
        $map['name'] = array('like','%'.$parkname.'%');     
        /* 获取数据 */
        $Park = $this->where($map)->limit(50)->select();

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
        $visitData = $VisitRecord->where('parkid = '.$parkid)->select();
        $Park["Visit"] = $visitData;

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
            $this->save($parkInfo);
            $result = $parkInfo['id'];
        } else {
            /* 添加停车场数据 */
            $parkInfo['creater'] = UID;
            $parkInfo['createtime'] = date('Y-m-d H:i:s');
            $parkInfo['updater'] = UID;
            if($this->create($parkInfo)){
                $result = $this->add();
            } else {
                return $this->getError(); //错误详情见自动验证注释
            }
        }


       return $result;
    }

 
}
