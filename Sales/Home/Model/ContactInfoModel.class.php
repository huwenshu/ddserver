<?php
 
use Think\Model;

/**
 * 销售人员模型
 */

class ContactInfoModel extends Model {

	/**
	 * 查询停联系人信息
	 * @param  string  $parkid 停车场id
	 */
	public function findContacts($parkid){
        $map = array(); 
        $map['parkid'] = $parkid;     
		/* 获取用户数据 */
		$Contacts = $this->where($map)->select();
        
		if(is_array($Contacts)){
            return $Contacts;
		} else {
			return NULL; //停车场不存在或被禁用
		}
	}

    /**
     * 保存联系人信息
     * @param  string  $parkid 停车场id
     */
    public function SaveContactInfo($parkid, $contactinfo){
        //先把数据全部删除
        $map = array(); 
        $map['parkid'] = $parkid;     
        $this->where($map)->delete();
        
        //再添加新数据
        $count = count($contactinfo['contactname']);
        $dataList = array();
        for ($i=0; $i < $count ; $i++) { 
            $dataList[] = array('parkid' => $parkid,'name' => $contactinfo['contactname'][$i], 'gender' => $contactinfo['contactgender'][$i],
                                'telephone' => $contactinfo['contactphone'][$i], 'job' => $contactinfo['contactjob'][$i]);
        }
        $result = $this->addAll($dataList); 

        if ($result) {
            return $dataList;
        }
        else{
            return false;
        }
        
    }

 
}
