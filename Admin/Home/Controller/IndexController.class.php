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
        $map = array();
        $map['pid'] =  array('NEQ',1);
        $result = $Money->where($map)->order('state,createtime desc')->select();
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
            $temp['visitype'] = $value['visitype'];
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
            $this->redirect('Home/Index/moneyReq');

        }
        else{
            $this->error('完成提现申请失败，请联系开发人员！');
        }

    }

    //礼品兑换请求
    public function giftreq(){
        $Gift = M('ExchangeGift');
        $map = array();
        $map['pid'] =  array('NEQ',1);
        $result = $Gift->where($map)->order('state,createtime desc')->select();
        $reqList = array();
        foreach($result as $key => $value){
            $temp['id'] = $value['id'];
            $temp['park'] = $this->getParkName($value['pid']);
            $temp['name'] = $value['name'];
            $temp['address'] = $value['address'];
            $temp['telephone'] = $value['telephone'];
            $temp['bankname'] = $value['bankname'];
            $temp['account'] = $value['account'];
            $temp['visitype'] = $value['visitype'];
            $temp['giftName'] = $this->getGiftName($value['gid']);
            $temp['giftType'] = $this->getGiftType($value['gid']);
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
            $this->redirect('Home/Index/giftReq');
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
        $condition1 = '(pid <>1 AND uid > 0 AND state = 2 AND updatetime < "'.$timeout1.'") OR (pid <>1 AND uid > 0 AND state in(0,1) AND updatetime < "'.$timeout2.'")';
        $outs = $ParkOrder->where($condition1)->order('updatetime')->select();

        //进场确认异常
        $ctime = time();
        $timeout3 = date("Y-m-d H:i:s",$ctime - 60*60);
        $condition2 = 'pid <>1 AND uid > 0 AND state = 0 AND updatetime < "'.$timeout3.'"';
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

    //包月零售异常状态订单
    public function monunusual(){

        $Model = new \Think\Model();
        $ctime = time();
        $timeout = date("Y-m-d 00:00:00",$ctime);
        //停车时间超过一天离场确认
        $sql = "SELECT o.id,o.pid,o.carid,o.cost,o.startime,p.name,d.telephone FROM dudu_park_order o, dudu_park_info p, dudu_driver_info d where o.pid = p.id and o.uid = d.id and o.state = 0 and startime < '".$timeout."' and p.corp_type =2";
        $list = $Model->query($sql);

        $ParkAdmin = M('ParkAdmin');
        $admin = array();
        foreach($list as $key => $value){
            $map = array();
            $map['parkid'] = $value['pid'];
            $temp = $ParkAdmin->where($map)->getField('id,nickname,phone',true);
            array_push($admin,$temp);
        }

        $this->list = $list;
        $this->admin = $admin;
        $this->meta_title = '离场异常 | 嘟嘟管理系统';
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



            //上传图片的配置
            $config = array(
                'maxSize'    =>    3145728,
                'rootPath'   =>   C('UPLOAD_FTP_PATH'),
                'savePath'   =>    '',
                'saveName'   =>    'gift'.time(),
                'exts'       =>    array('jpg', 'gif', 'png', 'jpeg'),
                'autoSub'    =>    false,
                'replace'      =>    true,
            );

            $upload = new \Think\Upload($config,'Ftp', C('UPLOAD_FTP'));// 实例化上传类


            //保存信息
            $giftInfo = array();
            $giftInfo['id'] = I('post.id');
            $giftInfo['name'] = I('post.name');
            $giftInfo['score'] = I('post.score');
            $giftInfo['valid'] = I('post.valid');
            $giftInfo['weight'] = I('post.weight');
            $giftInfo['type'] = I('post.type');


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


    public  function upfrontList(){
        $searchName = I('get.searchName');

        $ParkList = M('ParkInfo');
        $map = array();
        $map['name'] = array('like','%'.$searchName.'%');
        $parkList = $ParkList->where($map)->order('updatetime desc')->select();
        $this->parkList = $parkList;
        $this->meta_title = '首页 | 嘟嘟销售系统';
        $this->display();
    }

    public function upfrontInfo($parkid = null){
        if(IS_POST){
            $ParkInfo = M('ParkInfo');
            $data = array();
            $data['id'] = I('post.id');
            $data['upfront'] = I('post.upfront');
            $data['updater'] = 'a-'.UID;

            $ParkInfo->save($data);

            $this->redirect('Home/Index/upfrontInfo/parkid/'.I('post.id'));

        }
        else{
            $ParkList = M('ParkInfo');
            $map = array();
            $map['id'] = $parkid;
            $parkInfo = $ParkList->where($map)->getField('id,name,address,shortname,upfront');
            $this->parkInfo = $parkInfo[$parkid];
            $this->meta_title = '首页 | 嘟嘟销售系统';
            $this->display();
        }
    }


    public  function orderList(){
        $Model = new \Think\Model(); // 实例化一个model对象 没有对应任何数据表
        $sql = " SELECT o.id, o.uid, o.carid, o.startime, o.endtime, o.entrytime, o.leavetime, d.telephone, p.name, o.state FROM dudu_park_order o,  dudu_park_info p, dudu_driver_info d WHERE o.pid <> 1 and o.state > -1 and o.uid = d.id and o.pid = p.id ORDER BY o.updatetime DESC LIMIT 100";
        $orderList = $Model->query($sql);

        $PaymentRecord = M('PaymentRecord');
        foreach($orderList as $key => $value){
            $map = array();
            $map['oid'] = $value['id'];
            $map['state'] = 1;
            $records = $PaymentRecord->where($map)->order('createtime asc')->select();
            $pay = array();
            foreach($records as $k => $v){
                array_push($pay, $v['money']);
            }
            $orderList[$key]['pay'] = $pay;
        }
        $this->orders = $orderList;
        $this->display();
    }

    public  function makeLeave($oid){
        $ParkOrder = M('ParkOrder');
        $map = array();
        $map['id'] = $oid;
        $data = array();
        $data['state'] = 3;
        $data['leavetime'] = date('Y-m-d H:i:s');
        $data['driverleave'] = 2;//2表示是管理员后台处理离场
        $data['updater'] = 'a-'.UID;
        $ParkOrder->where($map)->save($data);

        $this->redirect('Home/Index/orderList/');
    }

    public  function modifyInfo(){
        if (IS_POST) {
            $email = I('post.email');
            $telephone = I('post.telephone');
            $pwd1 = I('post.pwd1');
            $pwd2 = I('post.pwd2');

            $AdminAuth = M('AdminAuth');

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
                $AdminAuth->where($map)->save($data);
            }
            else{
                $this->msg = " * 两次输入的密码不一致，请重新输入！";
            }


            $map = array();
            $map['id'] = UID;
            $adminInfo = $AdminAuth->where($map)->find();
            $this->adminInfo = $adminInfo;
            $this->meta_title = '修改信息 | 嘟嘟后台管理系统';
            $this->display();


        }
        else{
            $AdminAuth = M('AdminAuth');
            $map = array();
            $map['id'] = UID;
            $adminInfo = $AdminAuth->where($map)->find();

            $this->adminInfo = $adminInfo;
            $this->meta_title = '修改信息 | 嘟嘟后台管理系统';
            $this->display();
        }

    }


    //统计微信领取券的用户，哪些之前已经使用过了
    public function fetchWeixin(){
        $Model = new \Think\Model(); // 实例化一个model对象 没有对应任何数据表

        $weixinSQL = "SELECT uid FROM dudu_driver_giftlog WHERE code = 'F01C494F-4268-BA99-8BA0-A427465382FE' AND optype=1 AND fromid=11";
        $weixinT = $Model->query($weixinSQL);

        $usedSQL = "SELECT DISTINCT a.uid FROM `dudu_driver_coupon` a,`dudu_driver_giftpack` b, `dudu_driver_giftlog` c, `dudu_payment_record` d WHERE a.source = b.id and b.code=c.code and a.uid = c.uid and a.id = d.cid  AND a.status = 1 and c.optype = 1 and d.state = 1";
        $usedT =  $Model->query($usedSQL);

        $weixin = array();
        foreach($weixinT as $value){
            array_push($weixin, $value['uid']);
        }

        $used = array();
        foreach($usedT as $value){
            array_push($used, $value['uid']);
        }

        $sameArr = array_intersect($weixin, $used);
        $difA = array_diff($weixin, $used);
        $difB = array_diff($used, $weixin);

        echo "微信领取红包，且以前使用过红包的用户列表：<br/>";
        foreach($sameArr as $value){
            echo $this->getDriver($value)['telephone']."<br/>";
        }


        echo "<br/>微信领取红包，但是以前没有使用过红包的用户列表：<br/>";
        foreach($difA as $value){
            echo $this->getDriver($value)['telephone']."<br/>";
        }


    }

    //自动针对昨天下过订单的用户发送红包
    public function autoSend(){
        $today =  date("Y-m-d 00:00:00",strtotime("+1 day"));
        $yestoday =  date('Y-m-d 00:00:00');
        $Payment = M('PaymentRecord');
        $map = array();
        $map['state'] = 1;
        $map['createtime'] = array(array('EGT', $yestoday),array('LT', $today));
        $clist = $Payment->where($map)->select();
        $clist = $this->assoc_unique($clist, 'creater');//用户去重

        //缓存发送的情况，防止当天多次发送数据
        $list = $this->cacheAutoGift();

        if(!$list){//今天还没有发送过，可以发送红包
            $GiftPack = M('DriverGiftpack');
            foreach($clist as $key => $value){
                //$this->autoSendGift("873");
                $hcode = $this->autoSendGift($value['creater']);
                if($hcode){
                    $map = array();
                    $map['code'] = $hcode;
                    $info = $GiftPack->where($map)->getField('info');

                    $clist[$key]['hcode'] = $hcode;
                    $clist[$key]['info'] = $info;
                }
                else{//返回null，说明发送失败
                    unset($clist[$key]);
                }
            }
            $list = $this->cacheAutoGift($clist);
            $this->msg = "恭喜你，共发出 ".count($list)." 个红包!";
        }
        else{
             $this->msg = "抱歉！你今天已经发送过红包，共发出 ".count($list)." 个红包!";
        }

        sendMail('dubin@duduche.me',"[自动红包]", $this->msg);

        $this->list = $list;
        $this->meta_title = '自动红包 | 嘟嘟后台管理系统';
        $this->display();
    }
    
    public function mapvisit($files='20150629,20150630'){
        $excludes = array(//排除自己人的设备，或非来自设备的访问（第8字段）
                          8=>array('1d601e9ae58ed02dfdbbb8a1cd5a3fde92e0e34daaf7439e21cdfd013557fb74','ae4537a81c7c56518ae29a1b8d35f0f8','b96fa82c0d7f2c9fb006231673700119','b283b7837f84088172f652569dcd7751','')
                          );
        $gap = 0.4545;//50000m
        $names = explode(',',$files);
        $routelist = array();
        foreach($names as $name){
            $datas = readCSV('location2_'.$name,$excludes);
            foreach($datas as $data){
                if($data[3] != 0 && $data[4] != 0 && ($data[3] != $data[5] || $data[4] != $data[6]) && abs($data[3]-$data[5])<$gap && abs($data[4]-$data[6])<$gap){
                    //user search
                    $target = array('lat'=>$data[5],'lng'=>$data[6]);
                    $start = array('lat'=>$data[3],'lng'=>$data[4]);
                    $dup = false;
                    foreach($routelist as $route){
                        if($route['start']['lat'] == $start['lat'] && $route['start']['lng'] == $start['lng'] && $route['target']['lat'] == $target['lat'] && $route['target']['lng'] == $target['lng']){
                            $dup = true;
                            break;
                        }
                    }
                    if(!$dup){
                        $route = array('start'=>$start,'target'=>$target);
                        $routelist[] = $route;
                    }
                }
            }
        }
        $this->routelist=json_encode($routelist);
        $this->display();
    }
    
    public function mapstart($files='20150629,20150630'){
        $excludes = array(//排除自己人的设备，或非来自设备的访问（第8字段）
                          8=>array('1d601e9ae58ed02dfdbbb8a1cd5a3fde92e0e34daaf7439e21cdfd013557fb74','ae4537a81c7c56518ae29a1b8d35f0f8','b96fa82c0d7f2c9fb006231673700119','b283b7837f84088172f652569dcd7751','')
                          );
        $gap = 0.4545;//50000m
        $names = explode(',',$files);
        $plist = array();
        $Park = M('ParkInfo');
        foreach($names as $name){
            $datas = readCSV('location2_'.$name,$excludes);
            foreach($datas as $data){
                $start = array('lat'=>$data[3],'lng'=>$data[4]);
                if($data[3] == 0 || $data[4] == 0){
                    $start = array('lat'=>$data[5],'lng'=>$data[6]);
                }
                $dup = false;
                foreach($plist as $p){
                    if($p['lat'] == $start['lat'] && $p['lng'] == $start['lng']){
                        $dup = true;
                        break;
                    }
                }
                if(!$dup){
                    $gap = 0.004545;//0.002727;
                    $con = array();
                    $con['lat'] = array(array('gt',$start['lat'] - $gap),array('lt',$start['lat'] + $gap));
                    $con['lng'] = array(array('gt',$start['lng'] - $gap),array('lt',$start['lng'] + $gap));
                    $con['status'] = array('EGT', 4);
                    $count1 = $Park->where($con)->count();
                    $start['count']=$count1;
                    $plist[] = $start;
                }
            }
        }
        $this->plist=json_encode($plist);
        $this->display();
    }


}