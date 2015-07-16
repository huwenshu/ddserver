<?php
/**
 * 免费停车场审核控制器
 * User: Bin
 * Date: 15/6/9
 * Time: 下午2:04
 */



class FreeController extends BaseController{

    public function _initialize(){
        // 获取当前用户ID
        define('UID',$this->is_login());
        if( !UID ){// 还没登录 跳转到登录页面
            $this->redirect('Public/login');
        }
    }

    public function  index(){

        $searchName = I('get.search');

        $ParkFreeInfo = M('ParkFreeInfo');
        $map = array();
        $map['name'] = array('like','%'.$searchName.'%');
        $parkList = $ParkFreeInfo->where($map)->order('status, createtime desc')->select();
        $this->parkList = $parkList;
        $this->meta_title = '首页 | 嘟嘟销售系统';
        $this->display();
    }

    public function parkinfo($freeid =null, $fileError = null){
        if (IS_POST) {
            $Park = M('ParkFreeInfo');
            $u_park = $Park->where(array('id' => $freeid))->find();

            $parkInfo = array();
            //处理POST过来的信息
            $parkInfo['id'] = $freeid;
            $parkInfo['name'] = I('post.name');
            $tags = I('post.parktag');
            $note = "|";
            foreach ($tags as $key => $value) {
                $note = $note.$value.'|';
            }
            $parkInfo['note'] = $note;
            $parkInfo['dsc'] = I('post.dsc');
            $parkInfo['province'] = I('post.province');
            $parkInfo['city'] = I('post.city');
            $parkInfo['district'] = I('post.district');
            $parkInfo['lat'] = I('post.lat');
            $parkInfo['lng'] = I('post.lng');
            $parkInfo['status'] = I('post.status');

            //采用FTP方式，上传图片
            if($_FILES["parkimage"]["error"] === 0){//存在上传文件
                //上传图片的配置
                $config = array(
                    'maxSize'    =>    3145728,
                    'rootPath'   =>   C('PARK_UPLOAD_PATH'),
                    'savePath'   =>    '',
                    'saveName'   =>     'FreePark_'.I('post.id')."_".time(),
                    'exts'       =>    array('jpg', 'gif', 'png', 'jpeg'),
                    'autoSub'    =>    false,
                    'replace'      =>    true,
                );
                $upload = new \Think\Upload($config,'Ftp', C('UPLOAD_FTP'));// 实例化上传类
                $info   =   $upload->upload();
                if(!$info) {//上传错误
                    $fileError = $upload->getError();
                }
                else {//上传成功
                    //图片缩写的先去除
                    //$image = new \Think\Image();
                    //$imgURL = C('PARK_IMG_PATH').$info['parkimage']['savename'];
                    //$image->open($imgURL);
                    // 按照原图的比例生成一个最大为150*150的缩略图并保存为thumb.jpg
                    //$image->thumb(640, 300)->save($imgURL);
                    $parkInfo['image'] = $info['parkimage']['savename'];
                }
            }

            //保存

            $saved = $Park->save($parkInfo);

            if ($saved === false) {
                $this->error();
            }
            else{
                if($u_park['status'] == 0 && $parkInfo['status'] ==1){
                    $this->pushNotice($u_park['creater'],'恭喜您，您提交的免费停车场已经通过审核，嘟嘟感谢您为大家提供信息！',json_encode(array('r' => 'reload')));
                }

                $param = array('freeid' => $parkInfo['id']);
                if(isset($fileError)){
                    $param['fileError'] = $fileError;
                }
                $this->redirect('Free/parkinfo', $param, 0, '保存成功...');
            }

        }
        else{
            $FreePark = M('ParkFreeInfo');
            $map = array();
            $map['id'] = $freeid;
            $parkInfo = $FreePark->where($map)->find();
            $this->telephone = $parkInfo['creater'] == 0 ? "系统" : $this->getDriver($parkInfo['creater'])['telephone'];
            $this->parkInfo = $parkInfo;
            $this->fileError = $fileError;
            $this->meta_title = '免费停车场 | 嘟嘟后台管理';
            $this->display();
        }

    }

}