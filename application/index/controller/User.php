<?php 
namespace app\index\controller;

use app\common\controller\Base;
use app\common\model\User as UserModel;
use app\common\model\Article; 
use app\common\model\Comment;
use app\common\validate\User as UserValidate; 
use think\facade\Request; 
use think\facade\Session;
use app\index\service\Member;
use think\captcha\Captcha;

class User extends Base 
{	
	//注册页面
	public function register()
	{
		$this->assign('title','用户注册');
		return $this->fetch();
}

	//处理注册信息
	public function  insert()
	{	
		if($data = Request::post()){
			$rule = 'app\common\validate\Reginster'; 
			$res=$this->validate($data,$rule);
		  	if (true !== $res){
		  		$this->error($res,'register');
		  	}
		  	$mem = new Member;
		  	if($mem->mem_insert($data)){
				$this->success('注册成功！','index/index');
			} else {
				$this->error('请检查用户名密码！','register');			
			}			 
		}else{
			$this->error('请求类型错误','register');
		}
	}
	
	//登陆界面
	public function login()
	{
		$this->logined();
		return $this->view->fetch('login',['title'=>'用户登录']);
	}

	//验证码
	public function verify()
    {	
    	$config =    [
    	'fontSize'    =>    30,    
    	'length'      =>    5,   
   	 	'fontttf' 	  =>    '6.ttf',
   	 	'useCurve'	  =>	false,
   	 	'bg'=>[244, 244, 244],
	];
        $captcha = new Captcha($config);
        return $captcha->entry();    
    }

	//用户登录
	public function loginCheck()
	{		
		
		if(Request::isAjax())
		{
			$data = Request::post();
            $rule = 'app\common\validate\Login';
            $res=$this->validate($data,$rule);
            if (true !== $res){  
                return ['status'=> -1, 'message'=>$res];
            }
		  		$mem = new Member;
		  	switch($mem->mem_loginCheck($data))
		  	{
		  		case 1:
		  		return ['status'=>1, 'message'=>'登录成功！'];
		  		break;
		  		case 0:
		  		return ['status'=>0, 'message'=>'用户已被禁用！'];
		  		break;
		  		case -1:
		  		return ['status'=>-1, 'message'=>'检查用户名密码！'];	
		  		break;
		  	}
		}			 
}


	//退出登录
	public function logout()
	{
		Session::clear();
		$this->success('退出成功','index/index');
	}

	//个人空间
	public function show()
	{
		$param = Request::param('user_id');
		$where = ['id','=',$param];
		$data = UserModel::get($where);
		$userId = Session::get('user_id');
    	$artList = Article::where('user_id', $userId)->paginate(5);
    	$comList = Comment::where('session_id', $userId)->paginate(5);
		$this->assign('data',$data);
		$this->assign('artList',$artList);
		$this->assign('comList',$comList);
		$this->assign('empty','<h3>没有文章</h3>');
		$this->assign('em','<h3>没有评论</h3>');
		$this->assign('title','个人空间');
		return $this->fetch('user/member-show');
	}

	//删除评论
	public function doDelete()
     {
     	$artId = Request::param('id');
     	if(Comment::destroy($artId)){
     		$this->success('删除成功');
     	} 
     	$this->error('删除失败');
     }

     //个人信息修改页面
	public function memEdite()
		{
		$userId = Session::get('user_id');
		$data = UserModel::get($userId);
		$this->assign('data',$data);
		$this->assign('title','修改信息');
		return $this->fetch();
	}

	//处理个人信息修改
	public function  memEditeCheck()
	{	
		if($data = Request::post()){
			$rule = 'app\common\validate\MemEdite'; 
			$res=$this->validate($data,$rule);
		  	if (true !== $res){
		  		$this->error($res,'memEdite');
		  	}
		  	$mem = new Member;
		  	if($mem->mem_edite($data)){
				$this->success('修改成功！','memEdite');
			} else {
				$this->error('修改失败！','memEdite');			
			}			 
		}else{
			$this->error('请求类型错误','memEdite');
		}
	}


}

