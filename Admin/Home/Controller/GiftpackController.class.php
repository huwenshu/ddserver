<?php
/**
 * 红包管理控制器
 * User: Bin
 * Date: 15/3/26
 * Time: 下午12:27
 */

class GiftpackController extends BaseController{

    public function _initialize(){
        // 获取当前用户ID
        define('UID',$this->is_login());
        if( !UID ){// 还没登录 跳转到登录页面
            $this->redirect('Public/login');
        }
    }


    //推广人员
    public function  index(){
        $search = I('get.searchgift');

        $DriverGiftpak = M('DriverGiftpack');
        $map = array();
        $map['info'] = array('like','%'.$search.'%');
        $giftPack = $DriverGiftpak->where($map)->order('endtime desc')->select();
        $this->giftPack = $giftPack;
        $this->meta_title = '首页 | 嘟嘟销售系统';
        $this->display();
    }

    public  function  giftinfo($giftid = null){
        if (IS_POST) {
            $giftInfo = array();
            $code = $this->guid();
            //处理POST过来的信息
            $giftInfo['info'] = I('post.info');
            $giftInfo['uid'] = I('post.uid');
            $giftInfo['starttime'] = I('post.starttime');
            $giftInfo['endtime'] = I('post.endtime');
            $giftInfo['coupon_starttime'] = I('post.coupon_starttime');
            $giftInfo['coupon_endtime'] = I('post.coupon_endtime');
            $giftInfo['type'] = I('post.type');
            $giftInfo['minmoney'] = I('post.minmoney');
            $giftInfo['maxmoney'] = I('post.maxmoney');
            $giftInfo['maxnum'] = I('post.maxnum');

            $GiftPack = M('DriverGiftpack');
            $map = array();
            $map['id'] = I('post.id');
            $gift = $GiftPack->where($map)->find();
            if(is_array($gift)){
                $GiftPack->where($map)->save($giftInfo);
                $saveId = I('post.id');
            }
            else{
                $giftInfo['code'] = $code;
                $giftInfo['num'] = 0;
                $saveId = $GiftPack->add($giftInfo);
            }
            $this->redirect('/Home/Giftpack/giftinfo/giftid/'.$saveId.'/');

        }
        else {

            $GiftPack = M('DriverGiftpack');
            $map = array();
            $map['id'] = $giftid;
            $giftInfo = $GiftPack->where($map)->find();
            $this->giftInfo = $giftInfo;
            $this->meta_title = '停车场 | 嘟嘟销售系统';
            $this->display();
        }
    }

}