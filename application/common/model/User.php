<?php

namespace app\common\model;

use think\Model;


class User extends Model
{

    

    //数据库
    protected $connection = 'database';
    // 表名
    protected $name = 'user';
    
    // 自动写入时间戳字段
    protected $autoWriteTimestamp = 'int';

    // 定义时间戳字段名
    protected $createTime = 'createtime';
    protected $updateTime = false;
    protected $deleteTime = false;

    // 追加属性
    protected $append = [
        'status_text'
    ];
    

    
    public function getStatusList()
    {
        return ['normal' => __('Status normal'), 'hidden' => __('Status hidden')];
    }


    public function getStatusTextAttr($value, $data)
    {
        $value = $value ? $value : (isset($data['status']) ? $data['status'] : '');
        $list = $this->getStatusList();
        return isset($list[$value]) ? $list[$value] : '';
    }

    //获取该用户的所有下级
    public function getDownUser($user_id){
        $res=$this->where(['pid'=>$user_id])->select();
        $res=collection($res)->toArray();
        return $res;
    }




}
