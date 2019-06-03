<?php

namespace app\common\model;

use think\Model;


class Job extends Model
{

    

    //数据库
    protected $connection = 'database';
    // 表名
    protected $name = 'job';
    
    // 自动写入时间戳字段
    protected $autoWriteTimestamp = false;

    // 定义时间戳字段名
    protected $createTime = false;
    protected $updateTime = false;
    protected $deleteTime = false;

    // 追加属性
    protected $append = [
        //'worktime_text',
        'state_text'
    ];
    

    
    public function getStateList()
    {
        return ['1' => __('State 1'), '2' => __('State 2')];
    }


//    public function getWorktimeTextAttr($value, $data)
//    {
//        $value = $value ? $value : (isset($data['worktime']) ? $data['worktime'] : '');
//        return is_numeric($value) ? date("Y-m-d H:i:s", $value) : $value;
//    }


    public function getStateTextAttr($value, $data)
    {
        $value = $value ? $value : (isset($data['state']) ? $data['state'] : '');
        $list = $this->getStateList();
        return isset($list[$value]) ? $list[$value] : '';
    }

//    protected function setWorktimeAttr($value)
//    {
//        return $value === '' ? null : ($value && !is_numeric($value) ? strtotime($value) : $value);
//    }


}
