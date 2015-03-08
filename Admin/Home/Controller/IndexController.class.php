<?php

class IndexController extends BaseController {


    public function _initialize(){
        // 获取当前用户ID
        define('UID',$this->is_login());
        if( !UID ){// 还没登录 跳转到登录页面
            $this->redirect('Public/login');
        }
    }
    public function index(){
            $this->meta_title = '首页 | 嘟嘟销售系统';
            $this->display();
    }

    //提现请求列表
    public function moneyreq(){
        $Money = M('DrawMoney');
        $result = $Money->order('state,createtime desc')->select();
        $reqList = array();
        foreach($result as $key => $value){
            $temp['id'] = $value['id'];
            $temp['park'] = $this->getParkName($value['pid']);
            $temp['accountname'] = $value['accountname'];
            $temp['account'] = $value['account'];
            $temp['bankname'] = $value['bankname'];
            $temp['name'] = $value['name'];
            $temp['telephone'] = $value['telephone'];
            $temp['money'] = $value['money'];
            $temp['state'] = $value['state'];
            $temp['createtime'] = $value['createtime'];
            array_push($reqList, $temp);
        }
        $this->reqList = $reqList;
        $this->display();
    }

    //完成提现请求
    public function reqDone($rid){
        $Money = M('DrawMoney');
        $data['state'] = 1;
        $data['updater'] = 'a-'.UID;

        $map['id'] = $rid;
        $result = $Money->where($map)->save($data);

        if($result){
            $this->success('完成提现成功！', U('Index/moneyReq'));
        }
        else{
            $this->error('完成提现申请失败，请联系开发人员！');
        }

    }

    //礼品兑换请求
    public function giftreq(){

    }

}