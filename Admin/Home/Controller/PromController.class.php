<?php
/**

 * 推广管理控制器
 * @Bin
 * Date: 15/3/25
 * Time: 上午11:11
 */


class PromController extends BaseController{

    public function _initialize(){
        // 获取当前用户ID
        define('UID',$this->is_login());
        if( !UID ){// 还没登录 跳转到登录页面
            $this->redirect('Public/login');
        }
    }


    //推广人员
    public function  index(){

        $searchName = I('get.searchprom');

        $PromList = M('PromList');
        $map = array();
        $map['name'] = array('like','%'.$searchName.'%');
        $promList = $PromList->where($map)->select();
        $this->promList = $promList;
        $this->meta_title = '首页 | 嘟嘟销售系统';
        $this->display();
    }



    //推广人员
    public function prominfo($promid = null){

        if (IS_POST) {
            $prominfo = array();
            //处理POST过来的信息
            $prominfo['name'] = I('post.name');
            $prominfo['contact'] = I('post.contact');
            $prominfo['type'] = I('post.type');
            $prominfo['memo'] = I('post.memo');

            $PromList = M('PromList');
            $map = array();
            $map['id'] = I('post.id');
            $prom = $PromList->where($map)->find();
            if(is_array($prom)){
                $PromList->where($map)->save($prominfo);
                $savePromId = I('post.id');
            }
            else{
                $prominfo['ownerid'] = 0;
                $savePromId = $PromList->add($prominfo);
            }
            $this->redirect('/Home/Prom/prominfo/promid/'.$savePromId.'/');

        }
        else{
            $startime = I('get.startime');
            $endtime = I('get.endtime');

            $PromList = M('PromList');
            $map = array();
            $map['id'] = I('get.promid');
            $prom = $PromList->where($map)->find();
            $this->prominfo= $prom;

            $SalesAuth = M('SalesAuth');
            $map = array();
            $map['id'] = $prom['ownerid'];
            $ownername = $SalesAuth->where($map)->getField('username');
            $this->ownername = $ownername;


            $DriverGiftlog = M('DriverGiftlog');
            $map = array();
            $map['fromid'] = $promid;
            $giftCodes = $DriverGiftlog->distinct(true)->field('code')->where($map)->select();
            $promSum = array();
            foreach($giftCodes as $key => $value){
                $temp = array();
                $temp['code'] = $value['code'];
                $temp['info'] = $this->getGiftInfo($value['code']);

                $timeCon = "";
                if(!empty($startime)){
                    $timeCon = $timeCon." and DATEDIFF(optime , '$startime') >= 0";
                }
                if(!empty($endtime)){
                    $timeCon = $timeCon." and DATEDIFF('$endtime' , optime) >= 0";
                }

                $Model = new \Think\Model(); // 实例化一个model对象 没有对应任何数据表

                $checkSql = "SELECT COUNT(*) AS 'check' FROM dudu_driver_giftlog WHERE code = '".$value['code']."' AND optype=0 AND fromid=$promid".$timeCon;
                $check = $Model->query($checkSql)[0]['check'];
                $temp['check'] = $check;


                $openSql = "SELECT COUNT(*) AS 'open' FROM dudu_driver_giftlog WHERE code = '".$value['code']."'  AND optype=1 AND fromid=$promid".$timeCon;
                $open = $Model->query($openSql)[0]['open'];
                $temp['open'] = $open;


                $timeStr = "";
                if(!empty($startime)){
                    $timeStr = $timeStr." and DATEDIFF(d.updatetime , '$startime') >= 0";
                }
                if(!empty($endtime)){
                    $timeStr = $timeStr." and DATEDIFF('$endtime' , d.updatetime) >= 0";
                }
                $queryStr = "SELECT COUNT(*) AS 'use' FROM `dudu_driver_coupon` a,`dudu_driver_giftpack` b, `dudu_driver_giftlog` c, `dudu_payment_record` d WHERE a.source = b.id and b.code=c.code and a.uid = c.uid and a.id = d.cid and c.fromid =".$promid." AND a.status = 1 and c.optype = 1 and c.code ='".$value['code']."' and d.state = 1".$timeStr;
                $temp['use'] = $Model->query($queryStr)[0]['use'];

                array_push($promSum,$temp);


            }
            $this->promList = $promSum;
            $this->meta_title = '停车场 | 嘟嘟销售系统';
            $this->startime = $startime;
            $this->endtime = $endtime;
            $this->display();
        }

    }

}