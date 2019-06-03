<?php

namespace app\common\model;

use think\Model;


class Signup extends Model
{

    

    //数据库
    protected $connection = 'database';
    // 表名
    protected $name = 'signup';
    
    // 自动写入时间戳字段
    protected $autoWriteTimestamp = false;

    // 定义时间戳字段名
    protected $createTime = false;
    protected $updateTime = false;
    protected $deleteTime = false;

    // 追加属性
    protected $append = [
        'switch_text'
    ];
    

    
    public function getSwitchList()
    {
        return ['0' => __('Switch 0'), '1' => __('Switch 1')];
    }


    public function getSwitchTextAttr($value, $data)
    {
        $value = $value ? $value : (isset($data['switch']) ? $data['switch'] : '');
        $list = $this->getSwitchList();
        return isset($list[$value]) ? $list[$value] : '';
    }




}
