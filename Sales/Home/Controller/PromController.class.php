<?php
/**

 * 推广管理控制器
 * @Bin
 * Date: 15/3/25
 * Time: 上午11:11
 */


class PromController extends BaseController{


    //推广人员
    public function  index(){

        $searchName = I('get.searchprom');

        $PromList = M('PromList');
        $map = array();
        $map['ownerid'] = UID;
        $map['name'] = array('like','%'.$searchName.'%');
        $promList = $PromList->where($map)->select();
        $this->promList = $promList;
        $this->meta_title = '首页 | 嘟嘟销售系统';
        $this->display();
    }



    //推广人员
    public function prominfo($promid = null){

        if (IS_POST) {
            $promInfo = array();
            //处理POST过来的信息
            $prominfo['name'] = I('post.name');
            $prominfo['contact'] = I('post.contact');
            $prominfo['ownerid'] = UID;
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
                $savePromId = $PromList->add($prominfo);
            }
            $this->redirect('/Home/Prom/prominfo/promid/'.$savePromId.'/');

        }
        else{
            $PromList = M('PromList');
            $map = array();
            $map['id'] = $promid;
            $prom = $PromList->where($map)->find();
            $this->prominfo= $prom;


            $DriverGiftlog = M('DriverGiftlog');
            $map = array();
            $map['fromid'] = $promid;
            $giftCodes = $DriverGiftlog->distinct(true)->field('code')->where($map)->select();
            $promSum = array();
            foreach($giftCodes as $key => $value){
                $temp = array();
                $temp['code'] = $value['code'];
                $temp['info'] = $this->getGiftInfo($value['code']);

                $con = array();
                $con['code'] = $value['code'];
                $con['optype'] = 0;
                $con['fromid'] = $promid;
                $check = $DriverGiftlog->where($con)->count();
                $temp['check'] = $check;

                $con = array();
                $con['code'] = $value['code'];
                $con['optype'] = 1;
                $con['fromid'] = $promid;
                $open = $DriverGiftlog->where($con)->count();
                $temp['open'] = $open;

                $Model = new \Think\Model(); // 实例化一个model对象 没有对应任何数据表
                $queryStr = "SELECT COUNT(*) AS 'use' FROM `dudu_driver_coupon` a,`dudu_driver_giftpack` b, `dudu_driver_giftlog` c WHERE a.source = b.id and b.code=c.code and a.uid = c.uid and c.fromid =".$promid." AND a.status = 1 and c.optype = 1 and c.code ='".$value['code']."'";
                $temp['use'] = $Model->query($queryStr)[0]['use'];

                array_push($promSum,$temp);


            }
            $this->promList = $promSum;
            $this->meta_title = '停车场 | 嘟嘟销售系统';
            $this->display();
        }

    }

}