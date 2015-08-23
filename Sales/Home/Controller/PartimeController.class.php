<?php
/**
 * 兼职系统控制器
 * @Bin
 */
class PartimeController extends \Think\Controller{

    /**
     * 控制器访问控制函数
     */
    protected function init()
    {
        // 获取当前用户ID
        define('PTID', $this->is_login());
        if (!PTID) {// 还没登录 跳转到登录页面
            $this->redirect('Partime/login');
        }
    }

    public function index(){
        $this->init();
        $TaskParkInfo = M('TaskParkInfo');
        $map = array();
        $map['partime'] = PTID;
        $map['status'] = 2;
        $parks = $TaskParkInfo->where($map)->order('allocatedate asc, _address')->select();

        $this->parks = $parks;
        $this->display();
    }

    public function  tparkinfo(){
        $this->init();
        if(IS_POST){
            $tpid = I('post.tpid');
            $next = I('post.next');
            $id = $tpid;
            $name = I('post.name');
            $address = I('post.address');
            $latlng = I('post.latlng');
            $lat = explode(',', $latlng)[0];
            $lng = explode(',', $latlng)[1];
            $parking = I('post.parking');
            $chargingrules = I('post.chargingrules');
            $opentime = I('post.opentime');
            $styles = I('post.parkstyle');
            $parkstyle = "|";
            foreach ($styles as $key => $value) {
                $parkstyle = $parkstyle.$value.'|';
            }
            $note = I('post.note');
            $intention = I('post.intention');

            $data = array();
            $data['name'] = $name;
            $data['address'] = $address;
            $data['lat'] = $lat;
            $data['lng'] = $lng;
            $data['parking'] = $parking;
            $data['chargingrules'] = $chargingrules;
            $data['opentime'] = $opentime;
            $data['style'] = $parkstyle;
            $data['note'] = $note;
            $data['intention'] = $intention;
            $data['updater'] = 'PT-'.PTID;
            $TaskParkInfo = M('TaskParkInfo');
            if($id>0){
                $data['id'] = $id;
                $t= $TaskParkInfo->save($data);
            }
            else{
                $data['landmark'] = '兼职添加';
                $data['partime'] = PTID;
                $data['status'] = 2;
                $data['allocatedate'] = date('Y-m-d');
                $data['creater'] = 'PT-'.PTID;
                $data['createtime'] = date('Y-m-d H:i:s');
                $data['updater'] = 'PT-'.PTID;
                $t= $TaskParkInfo->add($data);
                $tpid = $t;
            }


            //下一个
            $map = array();
            $map['partime'] = PTID;
            $map['status'] = 2;
            $ids = $TaskParkInfo->where($map)->order('allocatedate asc, _address')->getField('id', true);
            $key = array_search($tpid, $ids);
            if($key+1 == count($ids)){
                $nextid = $ids[$key];
            }
            else{
                $nextid = $ids[$key+1];
            }

            if(empty($next)){
                $this->redirect('Partime/tparkinfo', array('tpid' => $tpid));
            }
            else{
                $this->redirect('Partime/tparkinfo', array('tpid' => $nextid));
            }
        }
        else{
            $tpid = I('get.tpid');
            $TaskParkInfo = M('TaskParkInfo');
            $map = array();
            $map['id'] = $tpid;
            $tparkinfo = $TaskParkInfo->where($map)->find();
            $this->tparkinfo = $tparkinfo;
            $this->tpid = $tpid;
            $this->display();
        }
    }

    /**
     * 兼职用户登录
     */
    public function login($username = null, $password = null){
        if(IS_POST){
            //调用 SalesAuth 模型的 login 方法，验证用户名、密码
            $PartTime = M('PartTime');
            $map = array();
            $map['name'] = $username;
            $map['iswork'] = 1;
            $partime = $PartTime->where($map)->find();
            if($partime === false){
                $ptid = 0;
            }
            elseif($partime === NULL){
                $ptid = -1;
            }
            else{
                $map['pwd'] = $password;
                $partime = $PartTime->where($map)->find();
                if($partime === false){
                    $ptid = 0;
                }
                elseif($partime === NULL){
                    $ptid = -2;
                }
                else{
                    $ptid = $partime['id'];
                }
            }
            if(0 < $ptid){ // 登录成功，$ptid 为登录的 PTID
                // session记录登录信息
                $auth = array(
                    'ptid'             => $ptid,
                    'username'        => $partime['name'],
                );
                session('partime_auth',$auth);
                $PHPSESSID = session_id();
                cookie('PHPSESSID',$PHPSESSID,30*24*3600);

                //跳转到登录前页面
                $this->redirect('Partime/index');
            } else { //登录失败
                switch($ptid) {
                    case -1: $error = '*用户不存在或被禁用！'; break; //系统级别禁用
                    case -2: $error = '密码错误！'; break;
                    default: $error = '未知错误！'; break; // 0-接口参数错误（调试阶段使用）
                }
                $this->error = $error;
                $this->display();
            }
        } else {
            if($this->is_login()){
                $this->redirect('Partime/index');
            }else{
                $this->display();
            }
        }
    }

    public function logout(){
        session('partime_auth', null);
        $this->redirect('Partime/login');
    }

    /**
     * 检测用户是否登录
     * @return integer 0-未登录，大于0-当前登录用户ID
     */
    protected function is_login(){
        $user = session('partime_auth');
        if (empty($user)) {
            return 0;
        } else {
            return  $user['ptid'];
        }
    }


}


?>