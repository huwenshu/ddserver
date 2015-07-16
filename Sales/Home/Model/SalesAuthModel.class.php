<?php
 
use Think\Model;

/**
 * 销售人员模型
 */

class SalesAuthModel extends Model {

	/**
	 * 用户登录认证
	 * @param  string  $username 用户名
	 * @param  string  $password 用户密码
	 */
	public function login($username, $password){
        $map = array(); 
        $map['username'] = $username;     
		/* 获取用户数据 */
		$Member = $this->where($map)->find();
        
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
                session('user_auth',$auth);
                $PHPSESSID = session_id();
                cookie('PHPSESSID',$PHPSESSID,30*24*3600);

				return $uid ; //登录成功，返回用户UID
			} else {
				return -2; //密码错误
			}
		} else {
			return -1; //用户不存在或被禁用
		}
	}


    /**
     * 注销当前用户
     * @return void
     */
    public function logout(){
        session('user_auth', null);
    } 
 
}
