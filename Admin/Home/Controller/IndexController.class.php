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
            $this->meta_title = '首页 | 嘟嘟后台管理系统';
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
    public function moneyreqDone($rid){
        $Money = M('DrawMoney');
        $data['state'] = 1;
        $data['updater'] = 'a-'.UID;

        $map['id'] = $rid;
        $result = $Money->where($map)->save($data);

        if($result){
            $draw = $Money->where($map)->find();
            $parkid = $draw['pid'];
            $change = $draw['money'];//提现金额

            $ParkInfo = M('ParkInfo');
            $con = array();
            $con['id'] = $parkid;
            $balance = $ParkInfo->where($con)->getField('balance');//获取账户余额

            $ParkInfo->where($con)->setDec('balance',$change); //账户余额更新

            $newMoney = $balance - $change; //更新过账户应有的值

            /*记录金钱变化到CSV文件*/
            $msgs = array();
            $msgs['ip'] = $_SERVER['REMOTE_ADDR'];//用户ip
            $msgs['parkid'] = $parkid;//停车场编号
            $msgs['uid'] = UID;//操作者id
            $msgs['opt'] = 5;//5-提现记录
            $msgs['oldValue'] = $balance;//原值
            $msgs['newValue'] = $newMoney;//新值
            $msgs['change'] = $change;//提取金额
            $msgs['note'] = $rid ;//补充信息，draw_money纪录id
            takeCSV($msgs);
            $this->success('完成提现成功！'.$newMoney, U('Index/moneyReq'));

        }
        else{
            $this->error('完成提现申请失败，请联系开发人员！');
        }

    }

    //礼品兑换请求
    public function giftreq(){
        $Gift = M('ExchangeGift');
        $result = $Gift->order('state,createtime desc')->select();
        $reqList = array();
        foreach($result as $key => $value){
            $temp['id'] = $value['id'];
            $temp['park'] = $this->getParkName($value['pid']);
            $temp['name'] = $value['name'];
            $temp['address'] = $value['address'];
            $temp['telephone'] = $value['telephone'];
            $temp['giftName'] = $this->getGiftName($value['gid']);
            $temp['adminName'] = $this->getAdmin($value['creater']);
            $temp['score'] = $value['score'];
            $temp['createtime'] = $value['createtime'];
            $temp['state'] = $value['state'];
            array_push($reqList, $temp);
        }
        $this->reqList = $reqList;
        $this->display();
    }

    //完成兑换请求
    public function giftreqDone($gid){
        $Gift = M('ExchangeGift');
        $data['state'] = 1;
        $data['updater'] = 'a-'.UID;

        $map['id'] = $gid;
        $result = $Gift->where($map)->save($data);

        if($result){
            $this->success('完成兑换礼品成功！', U('Index/giftReq'));
        }
        else{
            $this->error('完成提现兑换礼品失败，请联系开发人员！');
        }

    }

    //异常状态
    public function unusual(){
        $ParkOrder = M('ParkOrder');

        //离场确认异常
        $ctime = time();
        $timeout1 = date("Y-m-d H:i:s",$ctime - 30*60);
        $timeout2 = date("Y-m-d H:i:s",$ctime - 24*60*60);
        $condition1 = '(state = 2 AND updatetime < "'.$timeout1.'") OR (state in(0,1) AND updatetime < "'.$timeout2.'")';
        $outs = $ParkOrder->where($condition1)->order('updatetime')->select();

        //进场确认异常
        $ctime = time();
        $timeout3 = date("Y-m-d H:i:s",$ctime - 60*60);
        $condition2 = 'state = 0 AND updatetime < "'.$timeout3.'"';
        $ins = $ParkOrder->where($condition2)->order('updatetime')->select();


        $outList = array();
        foreach($outs as $key => $value){
            $temp = array();
            $temp['park'] = $this->getParkName($value['pid']);
            $temp['state'] = C('ORDER_STATE')[$value['state']];
            $temp['startime'] = $value['startime'];
            $temp['endtime'] = $value['endtime'];
            $temp['entrytime'] = $value['entrytime'];
            $temp['carid'] = $this->getDefualtCarid($value['uid']);
            $temp['telephone'] = $this->getDriver($value['uid'])['telephone'];
            $temp['oid'] = $value['id'];
            array_push($outList,$temp);

        }

        $inList = array();
        foreach($ins as $key => $value){
            $temp = array();
            $temp['park'] = $this->getParkName($value['pid']);
            $temp['state'] = C('ORDER_STATE')[$value['state']];
            $temp['startime'] = $value['startime'];
            $temp['endtime'] = $value['endtime'];
            $temp['entrytime'] = $value['entrytime'];
            $temp['carid'] = $this->getDefualtCarid($value['uid']);
            $temp['telephone'] = $this->getDriver($value['uid'])['telephone'];
            $temp['oid'] = $value['id'];
            array_push($inList,$temp);

        }

        $this->outList = $outList;
        $this->inList = $inList;
        $this->display();

    }

    //空车位8个小时以上未变动
    public function emptySpace(){

        $timeout = date("Y-m-d H:i:s",time() - 8*60*60);
        $ParkInfo = M('ParkInfo');
        $map = array();
        $map['laststateop'] = array('lt', $timeout);
        $parks = $ParkInfo->where($map)->order('parkstate,laststateop')->select();

        $result = array();
        foreach($parks as $key => $value){
            $temp = array();
            $temp['park'] = $value['name'];
            $temp['state'] = C('PARK_STATE')[$value['parkstate']];
            $temp['lastTime'] = $value['laststateop'];
            array_push($result, $temp);
        }

        $this->spaces = $result;
        $this->display();




    }

    //礼品列表
    public function giftList(){
        $GiftList = M('GiftList');
        $gifts = $GiftList->order('valid desc, weight')->select();
        $this->gifts = $gifts;
        $this->display();
    }

    //礼品信息
    public function giftInfo($gid = null, $fileError = ''){
        if (IS_POST) {

            // 上传图片
            $upload = new \Think\Upload();// 实例化上传类
            $upload->maxSize   = 3145728 ;// 设置附件上传大小
            $upload->exts      = array('jpg', 'gif', 'png', 'jpeg');// 设置附件上传类型
            $upload->rootPath  = './Public/Uploads/images/'; // 设置附件上传根目录
            $upload->savePath  = ''; // 设置附件上传（子）目录
            $upload->autoSub   = false;
            $upload->saveName  = 'gift'.time();
            $upload->replace = true;


            //保存信息
            $giftInfo = array();
            $giftInfo['id'] = I('post.id');
            $giftInfo['name'] = I('post.name');
            $giftInfo['score'] = I('post.score');
            $giftInfo['valid'] = I('post.valid');
            $giftInfo['weight'] = I('post.weight');


            $info   =   $upload->upload();
            if(!$info) {// 上传错误提示错误信息
                $fileError = $upload->getError();
            }
            else {
                $picInfo = $info['pic'];//上传成功
                $giftInfo['image'] = $picInfo['savename'];
            }



            $GiftList = M('GiftList');
            $map = array();
            $map['id'] = I('post.id');
            /* 获取礼品数据 */
            $Gift = $GiftList->where($map)->find();
            if(is_array($Gift)){
                /* 更新礼品数据 */
                $giftInfo['updater'] = UID;
                $GiftList->save($giftInfo);
                $gid =  $giftInfo['id'];
            } else {
                /* 添加礼品数据 */
                $giftInfo['creater'] = UID;
                $giftInfo['createtime'] = date('Y-m-d H:i:s');
                $giftInfo['updater'] = UID;
                $gid = $GiftList->add($giftInfo);
            }

           $this->redirect('Index/giftInfo', array('gid' => $gid,'fileError' => $fileError), 0, '保存成功...');
        }
        else{
            $GiftList = M('GiftList');
            $map['id'] = $gid;
            $giftInfo = $GiftList->where($map)->find();
            $this->giftInfo = $giftInfo;
            $this->imageRoot = C('GIFT_IMG');
            $this->fileError = urldecode($fileError);
            $this->display();

        }
    }



}