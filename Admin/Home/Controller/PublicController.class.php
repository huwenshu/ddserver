<?php
/**
 * Created by PhpStorm.
 * User: Bin
 * Date: 15/3/4
 * Time: 下午4:31
 */

class PublicController extends BaseController {

    /**
     * 后台用户登录
     */
    public function login($username = null, $password = null, $verify = null){
        if(IS_POST){
            // 检测验证码
            if(!$this->check_verify($verify)){
                //$this->error('验证码输入错误！');
            }

            //验证用户名、密码
            $map = array();
            $map['username'] = $username;
            /* 获取用户数据 */
            $Admin = M('AdminAuth');
            $Member = $Admin->where($map)->find();

            if(is_array($Member)){
                /* 验证用户密码 */
                if( $Member['password'] === strtoupper(md5($password))) {
                    //登录成功
                    $uid = $Member['id'];
                    $auth = array(
                        'uid'             => $Member['id'],
                        'username'        => $Member['username'],
                    );

                    //记录行为日志
                    //action_log('user_login', 'member', $uid, $uid);

                    // session记录登录信息
                    session('admin_auth', $auth);

                    $this->success('登录成功！', U('Index/index'));

                } else {
                    $this->error('密码错误');
                }
            } else {
                $this->error('用户不存在或被禁用');
            }
        } else {
            if($this->is_login()){
                $this->redirect('Index/index');
            }else{
                $this->display();
            }
        }
    }

    //退出登录 ,清除 session
    public function logout(){
        if($this->is_login()){
            session('admin_auth', null);
            session('[destroy]');
            $this->redirect('login');
        } else {
            $this->redirect('login');
        }
    }

    //生成 验证码
    public function verify(){
        $verify = new \Think\Verify();
        $verify->entry(1);
    }

}
