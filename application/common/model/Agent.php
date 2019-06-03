<?php

namespace app\common\model;

use think\Model;


class Agent extends Model
{

    

    //数据库
    protected $connection = 'database';
    // 表名
    protected $name = 'agent';
    
    // 自动写入时间戳字段
    protected $autoWriteTimestamp = 'int';

    // 定义时间戳字段名
    protected $createTime = 'createtime';
    protected $updateTime = false;
    protected $deleteTime = false;

    // 追加属性
    protected $append = [

    ];
    

    







    public function user()
    {
        return $this->belongsTo('User', 'user_id', 'id', [], 'LEFT')->setEagerlyType(0);
    }


    public function puser(){
        return $this->belongsTo('User','pid','id',[],'LEFT')->setEagerlyType(0);
    }


    public function getDownAgent($pid){
        $res=$this->where(['pid'=>$pid])->select();
        $res=collection($res)->toArray();
        return $res;
    }


    /*获取代理的下级代理
     *@params user_id 用户id
     * @params
     */
    public function getAgent($user_id,$level){
        if($level>3){
            return false;
        }
        $agent1=$this->getDownAgent($user_id);
        if($level==1){//下级
            return $agent1;
        }

        if($level==2){//下下级
            $agent2=[];
            foreach($agent1 as $k=>$v){
                $agent2_child=$this->getDownAgent($v['user_id']);
                if(!empty($agent2_child)){
                    $agent2[$k]=$agent2_child;
                }
            }
            $agent2 = array_reduce($agent2, function ($result, $value) {
                return array_merge($result, array_values($value));
            }, array());
            return $agent2;
        }

        if($level==3){//下下下级
            $agent2=[];
            foreach($agent1 as $k=>$v){
                $agent2_child=$this->getDownAgent($v['user_id']);
                if(!empty($agent2_child)){
                    $agent2[$k]=$agent2_child;
                }
            }
            $agent2 = array_reduce($agent2, function ($result, $value) {
                return array_merge($result, array_values($value));
            }, array());
            $agent3=[];
            foreach($agent2 as $k=>$v){
                $agent3_child=$this->getDownAgent($v['user_id']);
                if(!empty($agent3_child)){
                    $agent3[$k]=$agent3_child;
                }
            }
            $agent3 = array_reduce($agent3, function ($result, $value) {
                return array_merge($result, array_values($value));
            }, array());
            return $agent3;
        }

    }
    
}
