<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2019/5/27
 * Time: 22:17
 */
namespace wechat;


use fast\Http;
//use decrypt\WXBizDataCrypt;
class Wechat{

    public $appid;
    public $secret;

    public function __construct($appid,$secret)
    {
        $this->appid=$appid;
        $this->secret=$secret;
    }


    //获取accessToken
    public  function getAccessToken(){
        $url="https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential";
        $postdata=[
            'appid'=>$this->appid,
            'secret'=>$this->secret
        ];
        $res=Http::sendRequest($url,$postdata,'get');
        $res=json_decode($res['msg'],true);
        return $res;
    }


    //获取openid
    public  function getOpenId($js_code){
        $url="https://api.weixin.qq.com/sns/jscode2session";
        $postdata=[
            'appid'=>$this->appid,
            'secret'=>$this->secret,
            'js_code'=>$js_code,
            'grant_type'=>'authorization_code'
        ];
        $res=Http::sendRequest($url,$postdata,'get');
        $res=json_decode($res['msg'],true);
        return $res;
    }


    //获取手机号
    public function getPhoneNumber($sessionKey,$encryptedData,$iv){

//        $pc=new WXBizDataCrypt($this->appid,$sessionKey);
//        $errCode=$pc->decryptData($encryptedData,$iv,$data);
//        if($errCode==0){
//            return ['code'=>'success','msg'=>$data];
//        }else{
//            return ['code'=>'success','msg'=>$errCode];
//        }
    }


    //发送模板消息
    public  function sendTemplate($template,$info){
        $accessToken=$this->getAccessToken();
        $accessToken=$accessToken['access_token'];
        $temp=[];
        foreach($template['keywords'] as $k=>$v){
            $temp['keyword'.($k+1)]=array("value"=>$v,"color"=>"#173177");
        }
        $data['touser']=$info['openid'];
        $data['form_id']=$info['form_id'];
        $data['template_id']=$info['template_id'];//模板id
        $data['page']=$info['page'];//点击模板卡片后的跳转页面，仅限本小程序内的页面。支持带参数,该字段不填则模板无跳转。
        $data['data']=$temp;

        $url="https://api.weixin.qq.com/cgi-bin/message/template/send?access_token=".$accessToken;
        $res=Http::sendRequest($url,$data,'post');
        $res=json_decode($res['msg'],true);

        return $res;
    }

    /*
     * 获取小程序码 需要码数量极多的业务场景
     * @params scene 需要携带的参数
     * @params page 跳转小程序的页面
     * @params width 小程序码的大小 默认是430
     * @params is_hyaline(boolean) 是否需要透明底色的小程序码
     * */
    public function getUnlimited($scene,$page,$width,$is_hyaline){
        $accessToken=$this->getAccessToken();
        $accessToken=$accessToken['access_token'];

        $url="https://api.weixin.qq.com/wxa/getwxacodeunlimit?access_token=".$accessToken;
        empty($width)?$width=430:false;
        empty($is_hyaline)?$is_hyaline=false:false;
        $postdata=[
            'scene'=>$scene,
            'page'=>$page,
            'width'=>$width,
            'is_hyaline'=>$is_hyaline
        ];
        $postdata=json_encode($postdata);
        $res=Http::sendRequest($url,$postdata,'post');
        if($res['ret']==true){
            return ['code'=>'success','msg'=>$res['msg']];
        }else{
            return ['code'=>'error','msg'=>$res['msg']];
        }

    }



}