<?php 
namespace app\goods\common\model;

use think\Model;

class Brand extends Model 
{
	protected $pk = 'id';
	protected $table = 'bbs_goods_brand';
	protected $autoWriteTimestamp = true; 
	protected $createTime = 'create_time'; 
	protected $updateTime = 'update_time'; 
	protected $dateFormat = 'Y年m月d日';
    //自动完成设置
	protected $auto = [];
	// 仅新增时设置  
	protected $insert = ['create_time'];
	//仅更新时设置
	protected $update = ['update_time'];
}