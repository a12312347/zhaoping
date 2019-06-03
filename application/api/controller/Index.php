<?php

namespace app\api\controller;

use app\common\controller\Api;
use EasyWeChat\Core\Exception;
use fast\Http;
use decrypt\wxBizDataCrypt;
use think\Config;
use think\Db;
use wechat\Wechat;
/**
 * 首页接口
 */
class Index extends Api
{
    protected $noNeedLogin = ['*'];
    protected $noNeedRight = ['*'];

    /**
     * 首页
     *
     */
    public function index()
    {
        $this->success('请求成功');
    }



    /*
     * 获取openid
     *
     * */
    public function openid(){
        if(empty($this->request->request('code'))){
            return $this->error('请携带参数code!');
        }
        $appset=model('Config')->getGroupData('appset');
        $params=$this->request->get();

        $url="https://api.weixin.qq.com/sns/jscode2session";
        $postdata=[
            'appid'=>$appset['appid'],
            'secret'=>$appset['appsecret'],
            'js_code'=>$params['code'],
            'grant_type'=>'authorization_code'
        ];
        $res=Http::sendRequest($url,$postdata,'get');
        return $this->success('请求成功!',$res);
    }


    /*
     * 授权获取手机号
     *
     * */
    public function getPhoneNumber(){
        $params=$this->request->request();
        if(empty($params['encryptedData'])){
            return $this->error('请携带参数encryptedData!');
        }
        if(empty($params['iv'])){
            return $this->error('请携带参数iv!');
        }
        if(empty($params['sessionKey'])){
            return $this->error('请携带参数sessionKey!');
        }

        $appset=model('Config')->getGroupData('appset');
        $pc=new WXBizDataCrypt($appset['appid'],$params['sessionKey']);
        $errCode=$pc->decryptData($params['encryptedData'],$params['iv'],$data);
        if($errCode==0){
            return $this->success('请求成功!',$data);
        }else{
            return $this->error('请求失败!',$errCode);
        }
    }



    /*
     * 小程序用户登录
     *
     * */
    public function login(){
        $params=$this->request->request();
        $user=model('user')->get(['openid'=>$params['openid']]);
        if($user){
            return $this->success('请求成功1!',$user);
        }else{
            $params['createtime']=datetime(time());
            $res=model('user')->save($params);
            if($res){
                $user2=model('user')->get(['openid'=>$params['openid']]);
                return $this->success('请求成功2!',$user2);
            }

        }
    }


    /*
     * 首页轮播图
     *
     * */
    public function ad(){
        $adList=model('ad')->order('weigh','desc')->select();
        return $this->success('请求成功!',$adList);

    }


    /*
     * 搜索职位列表
     *
     * */
    public function searchJob(){
        $params=$this->request->request();
        if(!empty($params['keywords'])){
            $res=model('job')->where(['type'=>array('like','%'.$params['keywords'].'%')])->whereOr(['company'=>array('like','%'.$params['keywords'].'%')])->select();
        }else{
            $res=model('job')->select();
        }

        foreach($res as $k=>$v){
            $res[$k]['welfare']=model('Jobwelfare')->getWelfare($v['id']);
        }

        return $this->success('请求成功!',$res);
    }


    /*
     * 职位筛选栏  福利列表
     *
     * */
    public function welfareList(){
        $list=model('welfare')->select();
        return $this->success('请求成功!',$list);
    }



    /*
     * 返回所有职位的职位类型
     *
     * */
    public function JobType(){
        $type=model('job')->group('type')->field('type')->select();
        return $this->success('请求成功!',$type);
    }


    /*
     * 首页职位列表
     * 三种排列顺序  推荐(自然排序) 职位类型(根据已发布的职位类型) 智能筛选(福利 比如五险一金什么的)
     *
     * */
    public function jobList(){
        $params=$this->request->request();
        if(empty($params['page'])){
            return $this->error('请携带参数page!');
        }
        if(empty($params['welfare'])){//这是不带welfare参数的sql
            $sql="select * from fa_job where id >0 ";
            if(!empty($params['sort'])){
                $sql=$sql." and id != 0 ";
            }
            if(!empty($params['type'])){
                $sql=$sql." and type = '{$params['type']}' ";
            }
        }else{//这是带了welfare参数的sql
            $sql="select b.* from fa_job_welfare as a left join fa_job as b on a.job_id=b.id left join fa_welfare as c  on b.welfare=c.id where b.id>0 ";
            if(!empty($params['sort'])){
                $sql=$sql." and b.id !=0 ";
            }
            if(!empty($params['type'])){
                $sql=$sql." and b.type = '{$params['type']}' ";
            }
            $num=count( explode( ',' , $params['welfare'] ) );
            $sql=$sql." and a.welfare_id in ({$params['welfare']}) GROUP BY a.job_id HAVING COUNT(DISTINCT a.job_id,a.welfare_id)={$num} ";
        }
        $page=intval( max(1, $params['page'] ) );
        $pagesize=10;

        $select_sql=$sql." limit ".($page-1)*$pagesize." , ".$pagesize;

        $res=Db::query($select_sql);

        foreach($res as $k=>$v){
            $res[$k]['welfare']=model('Jobwelfare')->getWelfare($v['id']);
        }
        if($res){
            return $this->success('请求成功!',$res);
        }else{
            return $this->error('请求失败!');
        }
    }


    /*
     * 职位详情
     *@params id 职位的id
     * @params user_id 用户id
     * */
    public function jobDetails(){
        $params=$this->request->request();
        $res=model('job')->get(['id'=>$params['id']]);
        $jobrecord=model('jobrecord')->get(['job_id'=>$res['id'],'user_id'=>$params['user_id']]);
        $res['is_signup']=0;//未报名
        if($jobrecord){
            $res['is_signup']=1;//已报名
        }
        if($res){
            $res['welfare']=model('Jobwelfare')->getWelfare($res['id']);
        }
        return $this->success('请求成功!',$res);
    }



    /*
     * 动态轮播图
     *
     * */
    public function dynamicAd(){
        $res=model('dynamicad')->select();
        return $this->success('请求成功!',$res);
    }



    /*
     * 查看用户是否是代理
     *
     * */
    public function isAgent(){
        $params=$this->request->request();
        $res=model('agent')->get(['user_id'=>$params['user_id']]);
        if($res){
            return 1;
        }else{
            return 2;
        }
    }


    /*
     * 动态列表
     *
     * */
    public function dynamicList(){
        $params=$this->request->request();
        if(empty($params['type'])){
            return $this->success('请携带参数type!');
        }
        $res=model('dynamic')->where(['type'=>$params['type']])->field(['id','title','images'])->select();
        if($res){
            foreach($res as $v){
                $v['images']=explode(',',$v['images']);
            }
        }
        $res=collection($res)->toArray();
//        dump($res);exit;
        return $this->success('请求成功!',$res);
    }


    /*
     * 已邀请的用户
     * @params user_id 用户id
     *
     * */
    public function inviteUser(){
        $params=$this->request->request();
        $res=model('user')->where(['pid'=>$params['user_id']])->field(['id','nickname','openid','avatar'])->select();
        $count=model('user')->where(['pid'=>$params['user_id']])->count();
        return $this->success('请求成功!',['data'=>$res,'count'=>$count]);
    }


    /*
     * 查看邀请的代理
     * @params user_id 用户id
     * @params level 第几级代理
     * */
    public function inviteAgent(){
        $params=$this->request->request();
        $res=model('agent')->getAgent($params['user_id'],$params['level']);

        $result=[];
        foreach($res as $k=>$v){
            $result[]=Db::table('fa_agent')->alias('a')->join('fa_user b','a.user_id=b.id','LEFT')->where(['a.user_id'=>$v['user_id']])->field(['a.id','a.user_id','b.openid','b.nickname','b.avatar'])->find();
        }
        return $this->success('请求成功!',$result);

    }


    /*
     * 查看该用户邀请用户的收益
     *
     * */
    public function userRecommend(){
        $params=$this->request->request();
        //已邀请用户
        $inviteUsers=model('user')->where(['pid'=>$params['user_id']])->count();

        $inviteUsersID=model('user')->where(['pid'=>$params['user_id']])->field('id')->select();
        $inviteUsersID=collection($inviteUsersID)->toArray();
        $inviteUserIds=[];
        foreach($inviteUsersID as $k=>$v){
            $inviteUserIds[]=$v['id'];
        }

        //邀请收益
        $inviteRecommend=model('jobrecord')->where(['user_id'=>array('in',$inviteUserIds),'state'=>'pass'])->sum('recommend');

        //用户昨日收益
        //$inviteYesterday=model('jobrecord')->where(['user_id'=>array('in',$inviteUserIds),'state'=>'pass','createtime'=>array(array('gt' , date( 'Y-m-d' , time() - 3600 * 24 ) ),array('lt' , date('Y-m-d' , time())) )])->sum('recommend');
        $return=['inviteUsers'=>$inviteUsers , 'inviteRecommend'=> $inviteRecommend ];
        return $this->success('请求成功!',$return);

    }




    /*
     * 查看邀请代理的收益
     *
     * */
    public function AgentCommission(){
        $params=$this->request->request();
        //昨日新增代理
        $yesterdayAgent=model('agent')->where(['pid'=>$params['user_id'] , 'createtime'=>array( array('gt' , date('Y-m-d',time()-3600*24)), array('lt' , date('Y-m-d',time())) )])->count();
        //昨日新增用户数量
        $yesterdayUser=model('user')->where(['pid'=>$params['user_id'],'createtime'=>array( array('gt',date('Y-m-d',time()-3600*24)), array('lt',date('Y-m-d',time())))])->count();
        //昨日代理佣金收入
        $agentAmount=Db::table('fa_commission_record')->where(['user_id'=>$params['user_id'] , 'createtime'=>array( array('gt',date('Y-m-d',time()-3600*24)),array('lt',date('Y-m-d',time())))])->sum('amount');
        //历史收益
        $allAmount=model('Commissionrecord')->where(['user_id'=>$params['user_id']])->sum('amount');
        $return = [
            'yesterdayAgent'=>$yesterdayAgent,
            'yesterdayUser'=>$yesterdayUser,
            'agentAmount'=>$agentAmount,
            'allAmount'=>$allAmount
        ];
        return $this->success('请求成功!',$return);
    }


    /*
     *代理佣金明细
     * @params type yesterday=昨日收益 all=历史收益
     * @parmas user_id 代理的用户id
     * @params sort 默认按照时间降序排列  desc  asc
     * */
    public function AgentCommissionDetails(){
        $params=$this->request->request();
        if(empty($params['type'])){
            return $this->error('请携带参数type!');
        }
        empty($params['sort'])?$sort='desc':$sort=$params['sort'];//排序默认为降序排列
        if(!in_array($params['sort'],['desc','asc'])){
            return $this->error('sort参数请务必选择desc 或者asc!');
        }
        $order=$sort;

        $where=[];
        if($params['type']=='yesterday'){
            $where['createtime']=
                [
                    ['gt',date('Y-m-d',time()-3600*24)],
                    ['lt',date('Y-m-d',time())]
                ]
            ;
        }


        $list=model('Commissionrecord')->where(['user_id'=>$params['user_id'],'type'=>2])->where($where)->order('createtime',$order)->select();

        return $this->success('请求成功!',$list);
    }




    /*
     * 代理设置职位的直属佣金的列表
     *
     * */
    public function ajcList(){
        $params=$this->request->request();
        $agent=model('agent')->get(['user_id'=>$params['user_id']]);
        if(empty($agent)){
            return $this->error('用户不存在!');
        }
        $list=model('job')->field(['id','company','type','commission as origin_commission'])->select();//平台设置的
        if($agent['pid']==0){

            foreach($list as $k=>$v){
                $list[$k]['commission']=model('Agentjobcommission')->get(['user_id'=>$params['user_id'],'job_id'=>$v['id']])['commission'];
            }
        }else{

            foreach($list as $k=>$v){
                $list[$k]['up_commission']=model('Agentjobcommission')->get(['user_id'=>$agent['pid'],'job_id'=>$v['id']])['commission'];//上级设置的
                $list[$k]['commission']=model('Agentjobcommission')->get(['user_id'=>$params['user_id'],'job_id'=>$v['id']])['commission'];//你自己设置的
            }
        }


        $list=collection($list)->toArray();


        return $this->success('请求成功!',$list);
    }


    /*
     * 代理根据职位给直属下级设置代理佣金
     *
     * @params user_id 代理的用户id
     * @params job_id 职位的id
     * @params commission 代理佣金
     *
     * */
    public function setJobCommission(){
        $params=$this->request->request();
        if(empty($params['user_id']) || empty($params['job_id']) || empty($params['commission'])){
            return $this->error('请携带参数user_id,job_id,commission,pid!');
        }
        $job=model('job')->get(['id'=>$params['job_id']]);//查询出职位的一些基本信息，供后面判断
        $agent=model('agent')->get(['user_id'=>$params['user_id']]);
        //无论添加还是修改,都要判断重新设置的佣金是否比职位指定的高
        if($job['commission']<$params['commission']){
            return $this->error('佣金设置高于职位设置的佣金');
        }
        //还要查询出上级代理给这个职位设置的佣金
        $UpAgentInfo=model('Agentjobcommission')->get(['user_id'=>$agent['pid'] ,'job_id'=>$params['job_id']]);
//        dump($UpAgentInfo);exit;
        //如果代理是平台直属代理，则跳过，不是直属代理并且上级代理没有设置佣金
        if($agent['pid']!=0 && empty($UpAgentInfo)){
            return $this->error('上级设置代理佣金之后才可设置!');
        }
        //如果设置的佣金比上级设置的还高  就不能行
        if($params['commission'] > $UpAgentInfo['commission'] &&$agent['pid']!=0){
            return $this->error('不能设置的比上级代理设置的高!');
        }

        //然后现在才是开始 是添加还是修改设置直属下级的佣金
        //先查询下数据库看代理有没有设置过
        $jobcommission=model('Agentjobcommission')->get(['user_id'=>$params['user_id'],'job_id'=>$params['job_id']]);
        if($jobcommission){
            //如果是修改，佣金就只能设置得比上一次高
            if($params['commission'] < $jobcommission['commission']){
                return $this->error('佣金只能设置得比上一次高!');
            }
            //修改
            $res=model('Agentjobcommission')->where(['user_id'=>$params['user_id'] ,'job_id'=>$params['job_id']])->update(['pid'=>$agent['pid'],'commission'=>$params['commission']]);
            if($res){
                return $this->success('修改成功!');
            }else{
                return $this->error('修改失败!');
            }
        }else{
            //添加
            $res=model('Agentjobcommission')->save($params);
            if($res){
                return $this->success('添加成功!');
            }else{
                return $this->error('添加失败!');
            }
        }
        return $this->error('未知错误!');
    }



    /*
     *
     *为好友报名
     *
     * */
    public function ForFriendAdd(){
        $params=$this->request->request();
        $res=model('signup')->allowField(true)->save($params);
        if($res){
            return $this->success('添加成功!');
        }else{
            return $this->error('添加失败!');
        }
    }
    public function ForFriendEdit(){
        $params=$this->request->request();
        $signup=new \app\common\model\Signup;
        $signup->allowField(true)->save($params,['id'=>$params['id']]);

        if($signup){
            return $this->success('修改成功!');
        }else{
            return $this->error('修改失败!');
        }
    }




    /*
     *我要报名
     *
     *@params user_id 用户id
     * @params job_id 职位id
     * @params entry 入职奖
     * @params recommend 推荐奖
     * @params commission 佣金
     * */
    public function signUp(){
        $params=$this->request->request();
        $params['createtime']=datetime(time());
        $params['state']='wait';
        if(!empty($params['job_id'])){
            $job=model('job')->get(['id'=>$params['job_id']]);
            $params['entry']=$job['entry'];
            $params['recommend']=$job['recommend'];
            $params['commission']=$job['commission'];
        }

        $re=model('jobrecord')->get(['user_id'=>$params['user_id'],'job_id'=>$params['job_id']]);
        if($re){
            return $this->error('不能重复报名!');
        }
        $res=model('jobrecord')->allowField(true)->save($params);
        if($res){
            return $this->success('请求成功!');
        }else{
            return $this->error('请求失败!');
        }
    }



    /*
     * 我的求职
     *
     * @param user_id 用户id
     * */
    public function mySignUp(){
        $params=$this->request->request();
        if(empty($params['user_id'])){
            return $this->success('请携带参数user_id!');
        }
        //$res=model('jobrecord')->where(['user_id'=>$params['user_id']])->select();
        $res=Db::table('fa_job_record')->alias('a')->join('fa_job b','a.job_id=b.id','LEFT')->field(['a.id','b.id as job_id','b.company','b.type','b.entry','b.base_salary','a.createtime'])->select();
        return $this->success('请求成功!',$res);
    }


    /*
     * 取消报名
     *@params id
     *
     * */
    public function cancelSignUp(){
        $params=$this->request->request();
        $res=model('jobrecord')->where(['id'=>$params['id']])->delete();
        if($res){
            return $this->success('请求成功!');
        }else{
            return $this->error('请求失败!');
        }

    }


    /*
     * 动态详情
     * @params user_id 用户id
     * @params dynamic_id 动态id
     * */
    public function dynamicDetails(){
        $params=$this->request->request();
        $res=model('dynamic')->get(['id'=>$params['dynamic_id']]);
        $res['is_like']=0;
        $res['images']=explode(',',$res['images']);
        $re=Db::table('fa_likes')->where(['user_id'=>$params['user_id'],'dynamic_id'=>$params['dynamic_id']])->find();
        if($re){
            $res['is_like']=1;

        }

        if($res){
            return $this->success('请求成功!',$res);
        }else{
            return $this->error('请求失败1');
        }

    }


    /*
     * 给动态点赞
     * @params user_id 用户id
     * @params dynamic_id 动态id
     *
     * */
    public function like(){
        $params=$this->request->request();
        $params['createtime']=datetime(time());
        $info=Db::table('fa_likes')->where(['dynamic_id'=>$params['dynamic_id'],'user_id'=>$params['user_id']])->find();
        if($info){
            $res=Db::table('fa_likes')->where(['dynamic_id'=>$params['dynamic_id'],'user_id'=>$params['user_id']])->delete();
            if($res){
                return $this->success('取消点赞成功!');
            }else{
                return $this->error('取消失败!');
            }
        }
        $res=Db::table('fa_likes')->insert($params);
        if($res){
            return $this->success('点赞成功!');
        }else{
            return $this->error('点赞失败!');
        }
    }









    /*
     * 完成入职奖的打款
     * @params user_id 入职人员的用户id
     * @params job_id 入职的职位id
     *
     * */
    protected function completeEntry($user_id,$job_id){
        //入职的用户的信息
        $user=model('user')->get(['id'=>$user_id]);
        if(empty($user)){
            return ['code'=>'error','msg'=>'entry-用户不存在!'];
        }
        //职位的基本信息
        $job=model('job')->get(['id'=>$job_id]);
        if(empty($job)){
            return ['code'=>'error','msg'=>'entry-职位信息不存在!'];
        }
        $agent=model('agent')->get(['user_id'=>$user_id]);
        //判断该用户是否是代理  1为普通用户 2为代理
        if(empty($agent)){
            $agent=1;
        }else{
            $agent=2;
        }

            $res=Db::table('fa_user')->where(['id'=>$user_id])->setInc('balance',$job['entry']);
        if(!$res){
            return ['code'=>'error','msg'=>'入职打款失败!'];
        }
            //生成记录
            $data=[
                'user_id'=>$user_id,
                'pid'=>$user['pid'],
                'amount'=>$job['entry'],
                'note'=>'入职奖',
                'type'=>$agent,
                'createtime'=>datetime(time())
            ];
            $res2=Db::table('fa_commission_record')->insert($data);
        if(!$res2){
            return ['code'=>'error','msg'=>'入职奖记录生成失败!'];
        }
        return ['code'=>'success','msg'=>'入职奖成功!'];

    }



    /*
     * 推荐奖的打款
     * @params user_id
     * @params job_id
     *
     * */
    protected function completeRecommend($user_id,$job_id){
        //入职的用户的信息
        $user=model('user')->get(['id'=>$user_id]);
        if(empty($user)){
            return ['code'=>'error','msg'=>'entry-用户信息不存在!'];
        }
        //如果上级为平台 就不需要打款 跟生成打款记录了。。
        if($user['pid']==0){
            return ['code'=>'success','msg'=>'entry-用户上级为平台无需推荐奖!'];
        }
        //根据用户的上级id查询是否存在此用户 如果此用户不存在后续无法给上级用户打款
        $puser=model('user')->get(['id'=>$user['pid']]);
        if(empty($puser)){
            return ['code'=>'error','msg'=>'entry-上级用户不存在!'];
        }
        //职位的基本信息
        $job=model('job')->get(['id'=>$job_id]);
        if(empty($job)){
            return ['code'=>'error','msg'=>'entry-职位信息不存在!'];
        }
        //查看用户的上级是否是代理
        $agent=model('agent')->get(['user_id'=>$user['pid']]);
        //判断该用户是否是代理  1为普通用户 2为代理
        if(empty($agent)){
            $agent=1;
        }else{
            $agent=2;
        }


            //入职用户的上级打款推荐奖
            $res=Db::table('fa_user')->where(['id'=>$user['pid']])->setInc('balance',$job['recommend']);
        if(!$res){
            return ['code'=>'error','msg'=>'推荐奖打款失败!'];
        }
            //生成记录
            $data=[
                'user_id'=>$user['pid'],
                'pid'=>$puser['pid'],
                'amount'=>$job['recommend'],
                'note'=>'推荐奖',
                'type'=>$agent,
                'createtime'=>datetime(time())
            ];
            $res2=Db::table('fa_commission_record')->insert($data);
            if(!$res2){
                return ['code'=>'error','msg'=>'推荐打款记录生成失败!'];
            }
            return ['code'=>'success','msg'=>'推荐打款成功!'];
    }



    /*
     * 是否存在上级代理 返回代理的用户id
     *
     * */
    protected function isHaveAgent($user_id){
        $agent=Db::table('fa_user')->alias('a')->join('fa_agent b','a.id=b.user_id','LEFT')->where(['a.id'=>$user_id])->field(['a.pid','b.user_id'])->find();
        if(empty($agent['user_id'])&&empty($agent['pid'])){//自身pid为空或者为0 都表示已经没有上线了  user_id又表示不是代理所以直接跳出递归
            return false;
        }else if(empty($agent['user_id'])){
            return $this::isHaveAgent($agent['pid']);
        }else{
            return $agent['user_id'];
        }

    }


    /*
     * 是否存在上级代理设置的佣金  上级没设置就获取上上级的   上上级没有就上上上级  直到有为止
     *
     * */
    protected function isCommission($user_id,$job_id){
        function isCom($user_id,$job_id){
            $agent=Db::table('fa_agent')->where(['user_id'=>$user_id])->find();
            $pajc=Db::table('fa_agent_job_commission')->where(['user_id'=>$agent['pid'],'job_id'=>$job_id])->find();
            if(empty($pajc)){
                return isCom($agent['pid'],$job_id);
            }else if($agent['pid']==0){
                $jobcom=Db::table('fa_job')->where('id',$job_id)->find();
                return $jobcom['commission'];
            }else{
                return $pajc['commission'];
            }
        }
    }




    /*
     * 根据用户id打款给代理 以及所有上级代理
     * @params user_id
     * @params job_id
     * */
    protected function completeCommission($user_id,$job_id){
        //获取代理上级或者上上级到平台设置的佣金
        function isCom($user_id,$job_id){
            $agent=Db::table('fa_agent')->where(['user_id'=>$user_id])->find();
            $pajc=Db::table('fa_agent_job_commission')->where(['user_id'=>$agent['pid'],'job_id'=>$job_id])->find();
            if(empty($pajc)&&$agent['pid']!=0){
                return isCom($agent['pid'],$job_id);
            }else if($agent['pid']==0){
                $jobcom=Db::table('fa_job')->where('id',$job_id)->find();
                return $jobcom['commission'];
            }else{
                return $pajc['commission'];
            }
        }
        //打款给用户
        function comA($user_id,$job_id,$agent_user_id){

            $agent=Db::table('fa_agent')->where(['user_id'=>$user_id])->find();

            //当前用户设置的直属下级佣金
            $ajc=Db::table('fa_agent_job_commission')->where(['user_id'=>$user_id,'job_id'=>$job_id])->find();//当前用户设置的直属下级佣金

            //当前用户已经是平台直属用户了  上级设置佣金直接从job表获取
            if($agent['pid']==0){
                $pajc=Db::table('fa_job')->where(['id'=>$job_id])->find();
            }else{
                //$pajc=Db::table('fa_agent_job_commission')->where(['user_id'=>$ajc['pid'],'job_id'=>$job_id])->find();//当前用户的上级设置的直属下级的佣金
                $pajc['commission']=isCom($user_id,$job_id);
            }

            empty($ajc)?$ajc['commission']=0:false;

            //用户入职 用户的上级中的第一个代理 可以直接获得他上级设置的所有佣金
            if(!empty($agent_user_id)&&$agent_user_id==$user_id){
                $commission=$pajc['commission'];
            }else{
                $commission=$pajc['commission']-$ajc['commission'];
            }


//            dump($commission);exit;
            //给代理打款
            $res=Db::table('fa_user')->where(['id'=>$user_id])->setInc('balance',$commission);
            //如果给代理打款失败就直接返回false
            if(!$res){
                return ['code'=>'error','msg'=>'代理打款失败!'];
            }

            $data=[
                'user_id'=>$user_id,
                'pid'=>$agent['pid'],
                'amount'=>$commission,
                'note'=>'代理佣金',
                'type'=>2,
                'createtime'=>datetime(time())
            ];

            $res2=Db::table('fa_commission_record')->insert($data);
            if(!$res2){
                return ['code'=>'error','msg'=>'代理佣金记录生成失败!'];
            }
            //如果当前用户的pid=0的话 就直接返回true 表示整个流程完美结束
            if($agent['pid']==0){
                return ['code'=>'success','msg'=>'代理打款成功!'];
            }else{
                return comA($agent['pid'],$job_id,0);
            }
        }

        $agent_user_id=$this::isHaveAgent($user_id);
        return comA($agent_user_id,$job_id,$agent_user_id);
    }




    /*
     * 完成用户入职之后的打款  入职奖 推荐奖 佣金
     * @params user_id 用户id
     * @params job_id 职位id
     *
     * */
    public function completeJob(){
        $params=$this->request->request();
        Db::startTrans();
        try{
            $entry=$this::completeEntry($params['user_id'],$params['job_id']);
            $recommend=$this::completeRecommend($params['user_id'],$params['job_id']);
            $commission=$this::completeCommission($params['user_id'],$params['job_id']);
            if($entry['code']=='error' ||$recommend['code']=='error'||$commission['code']=='error'){
                Db::rollback();
                $res='error';
            }else{
                Db::commit();
                $res='success';
            }

        }catch(\Exception $e){
            dump($e->getMessage());
            Db::rollback();
            $res='error';
        }

        if($res=='success'){
            return $this->success('请求成功!');
        }else{
            return $this->error('请求失败!'.$entry['msg'].$recommend['msg'].$commission['msg']);
        }

    }


    //保存form_id
    public function saveFormId(){
        $params=$this->request->request();
        $res=Db::table('fa_formid')->insert(['form_id'=>$params['form_id'],'openid'=>$params['openid'],'createtime'=>datetime(time())]);
        if($res){
            return $this->success('请求成功!');
        }else{
            return $this->error('请求失败!');
        }
    }








    /*
     * 企业付款给用户
     *
     *
     * */
    public function transfers(){
        //$params=$this->request->request();
        //企业付款到零钱
        $wxpay=new \wxpay\Wxpay();
        $config=new \app\common\model\Config();
        $params=[];
        $appset=$config->getGroupData('appset');
        $wxpayset=$config->getGroupData('wxpayset');
        $params['appid']=$appset['appid'];
        $params['mch_id']=$wxpayset['mch_id'];
        $params['secret']=$wxpayset['apisecret'];
        $params['ip']=$wxpayset['ip'];
        $params['name']='陈林';
        $params['amount']=0.01;
        $params['openid']='asdasdasdasdasdas';
        $params['desc']='余额提现';

        $res=$wxpay->transfers($params);
        dump($res);
    }



    public function  test(){

        $params=$this->request->request();
        $appset=model('Config')->getGroupData('appset');
        $wechat=new Wechat($appset['appid'],$appset['appsecret']);
        //$res=$wechat->getPhoneNumber($params['sessionKey'],$params['encryptedData'],$params['iv']);
        $data['scene']='a=1';
        $data['page']='';
        $res=$wechat->getUnlimited($data['scene'],$data['page'],'','');
        dump($res);


    }









}
