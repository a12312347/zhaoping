<?php

namespace app\admin\controller;

use app\common\controller\Backend;
use EasyWeChat\Core\Exception;
use think\Db;
use think\Config;

/**
 * 用户提现申请管理
 *
 * @icon fa fa-circle-o
 */
class Txrecord extends Backend
{
    
    /**
     * Txrecord模型对象
     * @var \app\common\model\Txrecord
     */
    protected $model = null;

    public function _initialize()
    {
        parent::_initialize();
        $this->model = new \app\common\model\Txrecord;
        $this->view->assign("stateList", $this->model->getStateList());
    }
    
    /**
     * 默认生成的控制器所继承的父类中有index/add/edit/del/multi五个基础方法、destroy/restore/recyclebin三个回收站方法
     * 因此在当前控制器中可不用编写增删改查的代码,除非需要自己控制这部分逻辑
     * 需要将application/admin/library/traits/Backend.php中对应的方法复制到当前控制器,然后进行修改
     */
    

    /**
     * 查看
     */
    public function index()
    {
        //当前是否为关联查询
        $this->relationSearch = true;
        //设置过滤方法
        $this->request->filter(['strip_tags']);
        if ($this->request->isAjax())
        {
            //如果发送的来源是Selectpage，则转发到Selectpage
            if ($this->request->request('keyField'))
            {
                return $this->selectpage();
            }
            list($where, $sort, $order, $offset, $limit) = $this->buildparams();
            $total = $this->model
                    ->with(['user'])
                    ->where($where)
                    ->order($sort, $order)
                    ->count();

            $list = $this->model
                    ->with(['user'])
                    ->where($where)
                    ->order($sort, $order)
                    ->limit($offset, $limit)
                    ->select();

            foreach ($list as $row) {
                $row->visible(['id','user_id','tx_cost','sj_cost','createtime','state']);
                $row->visible(['user']);
				$row->getRelation('user')->visible(['nickname','avatar','tel','name']);
            }
            $list = collection($list)->toArray();
            $result = array("total" => $total, "rows" => $list);

            return json($result);
        }
        return $this->view->fetch();
    }


    /*
     * 通过申请
     *
     * */
    public function pass(){
        if($this->request->get()){
            $params=$this->request->get();
            $user=model('user')->get(['id'=>$params['user_id']]);

            Db::startTrans();
            $res=Db::table('fa_txrecord')->where(['id'=>$params['id']])->update(['state'=>'pass']);
            if($res){
                Db::commit();
                return $this->success('操作成功!');
            }else{
                Db::rollback();
                return $this->error('操作失败!');
            }
        }
    }


    /*
     * 拒绝申请
     *
     * */
    public function refuse(){
        if($this->request->get()){
            $params=$this->request->get();
            $user=model('user')->get(['id'=>$params['user_id']]);

            Db::startTrans();
            $res='';
            try{

                Db::table('fa_txrecord')->where(['id'=>$params['id']])->update(['state'=>'refuse']);
                Db::table('fa_user')->where(['id'=>$params['user_id']])->setInc('balance',$params['tx_cost']);
                Db::commit();
                $res='success';

            }catch(\Exception $e){
                Db::rollback();
                $res='error';
            }
            if($res=='success'){
                return $this->success('操作成功!');
            }else{
                return $this->error('操作失败!');
            }

        }
    }













}
