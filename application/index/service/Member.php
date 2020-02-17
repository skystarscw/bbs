<?php
namespace app\index\service;

use app\common\controller\Base;
use think\facade\Session;
use app\common\model\User as UserModel; 
use think\facade\Request; 
use app\common\model\RechangeCheck;
use app\admin\common\model\Finance; 

class Member extends Base
{
	/*
        注册   
     */
    public function mem_insert($data)
	{	
        $data['encrypt'] = random(6);
        $data['password'] = $this->create_password($data['password'],$data['encrypt']);
         $file = Request::file('image');
                $info = $file -> validate([
                    'size'=>5000000000, 
                    'ext'=>'jpeg,jpg,png,gif' 
                ]) -> move('uploads/');
                if ($info) {
                    $data['image'] = $info->getSaveName();

                } else {
                    $this->error($file->getError(),'index/user/register');
                }
		if($user=UserModel::create($data))
		{
		  $courentUser = UserModel::get($user->id);
          $moneyData = array('uid' => $courentUser->id, 'grade' => 1, 'agency_level' => 1,'money' =>0, 'time' => time());
          $money_id = Finance::create($moneyData);
          Session::set('user_id',$courentUser->id);
		  Session::set('user_name',$courentUser->name);
		  Session::set('is_admin',$courentUser->is_admin);	
		  return 1;	
		}
	}
	/*
        前台登录   
     */
    public function mem_loginCheck($data)
	{
		$result = UserModel::get(
                    function($query) use ($data){
                    $query->where('name',$data['name']);
                }
            );
		if (!$result || $this->create_password($data['password'],$result['encrypt']) != $result['password']) {
            return -1;
        }
        if ($result->status == 1) {
        	$data['login_num'] = $result['login_num'] + 1;
        	$data['login_time'] = time();
        	$hja = UserModel::where('name',$data['name'])
        	->update(['login_time'=>$data['login_time'],'login_num'=>$data['login_num']]);
            Session::set('user_id', $result->id);
			Session::set('user_name', $result->name);
			Session::set('is_admin', $result->is_admin);
			return 1;
        }else{
        	return 0;
        }
		
	}
    /*
        后台登录   
     */
    public function mem_loginCheck_admin($data)
    {
        $result = UserModel::get(
                    function($query) use ($data){
                    $query->where('name',$data['name']);
                }
            );
        if (!$result || $this->create_password($data['password'],$result['encrypt']) != $result['password']) {
            $this->success('登录失败，请检查用户名密码！','admin/user/login');
        }
        if ($result->status == 1) {
            $data['login_num'] = $result['login_num'] + 1;
            $data['login_time'] = time();
            $hja = UserModel::where('name',$data['name'])
            ->update(['login_time'=>$data['login_time'],'login_num'=>$data['login_num']]);
            Session::set('user_id', $result->id);
            Session::set('user_name', $result->name);
            Session::set('is_admin', $result->is_admin);
            $this->success('登录成功','admin/user/userList');
        }else{
            $this->success('登录失败，请重试！','admin/user/login');
        } 
        
    }

    /*
        编辑用户信息（密码）
     */
    public function mem_update_password($data,$encrypt)
    {
        return $this->create_password($data,$encrypt);
    }    

    /*
        密码加密
     */
    public function create_password($password,$encrypt) {
        $salt = '$2a$11$' . substr(md5($password.$encrypt), 5, 23);//Blowfish
        return substr(crypt($password, $salt),7);
    }

        /*
        个人信息修改 
     */
    public function mem_edite($data)
    {   
         $file = Request::file('image');
         if ($file)
         {
            $info = $file -> validate([
                    'size'=>5000000000, 
                    'ext'=>'jpeg,jpg,png,gif' 
                ]) -> move('uploads/');
                if ($info) 
                {
                    $data['image'] = $info->getSaveName();

                } else {
                    $this->error($file->getError(),'index/User/memEdite');
                }
         }
                $user = new UserModel;
                $result = $user->save($data,['id','=',$data['id']]);
        if($result)
        {
          return 1; 
        }
    }
    public function mem_Charge($data)
    {   
        $data['money'] = $data['amount'];
        $rule = ['money|总额' => 'require|Num'];
        $res=$this->validate($data,$rule);
        if (!true == $res){
            $this->error($res,'memMoney');
        }
        $mem = new RechangeCheck;
        $param = [
            'uid'     =>      Session::get('user_id'),
            'money'   =>      $data['money'],
            'type'    =>      1,
    ];
        $result = $mem->save($param);
        if($result)
        {
          return 1; 
        }
    }

}

