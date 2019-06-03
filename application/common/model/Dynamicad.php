<?php

namespace app\common\model;

use think\Model;


class Dynamicad extends Model
{

    

    //数据库
    protected $connection = 'database';
    // 表名
    protected $name = 'dynamic_ad';
    
    // 自动写入时间戳字段
    protected $autoWriteTimestamp = false;

    // 定义时间戳字段名
    protected $createTime = false;
    protected $updateTime = false;
    protected $deleteTime = false;

    // 追加属性
    protected $append = [

    ];
    

    public function dynamic(){
        return $this->belongsTo('Dynamic','dynamic_id','id',[],'LEFT')->seteagerlyType(0);
    }







}
