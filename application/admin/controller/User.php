<?php

namespace app\admin\controller;

use app\common\controller\Backend;
use GuzzleHttp\Psr7\Request;
use think\Db;

/**
 * 用户管理
 *
 * @icon fa fa-user
 */
class User extends Backend
{

    /**
     * User模型对象
     * @var \app\common\model\User
     */
    protected $model = null;
    protected $multiFields = 'is_ban';

    public function _initialize()
    {
        parent::_initialize();
        $this->model = new \app\common\model\User;
        $this->view->assign("statusList", $this->model->getStatusList());
    }

    /**
     * 默认生成的控制器所继承的父类中有index/add/edit/del/multi五个基础方法、destroy/restore/recyclebin三个回收站方法
     * 因此在当前控制器中可不用编写增删改查的代码,除非需要自己控制这部分逻辑
     * 需要将application/admin/library/traits/Backend.php中对应的方法复制到当前控制器,然后进行修改
     */



    /**
     * 排除前台提交过来的字段
     * @param $params
     * @return array
     */
    protected function preExcludeFields($params)
    {
        if (is_array($this->excludeFields)) {
            foreach ($this->excludeFields as $field) {
                if (key_exists($field, $params)) {
                    unset($params[$field]);
                }
            }
        } else {
            if (key_exists($this->excludeFields, $params)) {
                unset($params[$this->excludeFields]);
            }
        }
        return $params;
    }





    /**
     * 查看
     */
    public function index()
    {
        //当前是否为关联查询
        $this->relationSearch = false;
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

                    ->where($where)
                    ->order($sort, $order)
                    ->count();

            $list = $this->model

                    ->where($where)
                    ->order($sort, $order)
                    ->limit($offset, $limit)
                    ->select();

            foreach ($list as $row) {
                $row->visible(['id','nickname','avatar','openid','createtime','status','is_ban','wechat','tel','name','balance']);

            }
            $list = collection($list)->toArray();
            foreach($list as $k=>$v){
                $list[$k]['user_agent']=count(model('agent')->get(['user_id'=>$v['id']]));

            }
            $result = array("total" => $total, "rows" => $list);

            return json($result);
        }
        return $this->view->fetch();
    }






    /*
     * 把用户设置为代理商
     *
     * */
    public function agent(){
        if($this->request->get()){
            $params=$this->request->get();

            $agent=model('agent')->get(['user_id'=>$params['id']]);

            if($agent){
                return $this->error('该用户已成为代理!');
            }

            $res=model('agent')->save(['user_id'=>$params['id'],'pid'=>0]);
            if($res){
                return $this->success('更改成功!');
            }
                return $this->error('更改失败!');
        }
    }


    /*
     * 取消代理商
     *
     * */
    public function delagent(){
        if($this->request->get()){
            $params=$this->request->get();
            $res=model('agent')->where(['user_id'=>$params['id']])->delete();
            if($res){

                return $this->success('取消成功!');
            }
                return $this->error('取消失败!');

        }
    }


}
