<?php

namespace app\common\model;

use think\Model;


class Jobwelfare extends Model
{



    //数据库
    protected $connection = 'database';
    // 表名
    protected $name = 'job_welfare';

    // 自动写入时间戳字段
    protected $autoWriteTimestamp = false;

    // 定义时间戳字段名
    protected $createTime = false;
    protected $updateTime = false;
    protected $deleteTime = false;

    // 追加属性
    protected $append = [

    ];



    public function job(){
        return $this->belongsTo('Job','job_id','id',[],'LEFT')->setEagerlyType(0);
    }

    public function welfare(){
        return $this->belongsTo('Welfare','welfare_id','id',[],'LEFT')->setEagerlyType(0);
    }

    public function getWelfare($job_id){
        $res=$this->alias('a')->join('fa_job b','a.job_id=b.id','LEFT')->join('fa_welfare c','a.welfare_id=c.id','LEFT')->where(['b.id'=>$job_id])->field('c.name')->select();
        return collection($res)->toArray();
    }







}
