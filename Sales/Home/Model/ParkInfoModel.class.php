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

        $con = array();

        $SalesAuth= M('SalesAuth');
        $map = array();
        $map['id'] = UID;
        $leader =  $SalesAuth->where($map)->getField('leader');

        if($leader != 0){//没有上级的可以看到所有停车场,否则只能看到自己
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

            $con['responsible'] = array('in',$sales);
        }

        $where = array();
        $where['name']  = array('like','%'.$parkname.'%');
        $where['address']  = array('like','%'.$parkname.'%');
        $where['_logic'] = 'or';
        $con['_complex'] = $where;

        /* 获取数据 */
        $Park = $this->where($con)->order('updatetime desc')->select();

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
            $PinYin = new Home\Common\PinYin();
            $pinYin = strtoupper($PinYin->getFirstPY($parkInfo['name']));
            $shortName = $this->getShort($pinYin, 0);
            $parkInfo['shortname'] = $shortName;

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

    //找出一个没有重复的停车场缩写
    private function getShort($pinyin, $i=0){
        if($i == 0){
            $str = $pinyin;
        }
        else{
            $str = $pinyin.$i;
        }

        $ParkInfo = M('ParkInfo');
        $map = array();
        $map['shortname'] = $str;
        $park = $ParkInfo->where($map)->find();
        if(is_array($park)){
            $i++;
            return $this->getShort($pinyin,$i);
        }
        else{
            return $str;
        }

    }
 
}
