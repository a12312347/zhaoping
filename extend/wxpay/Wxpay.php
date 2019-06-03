<?php
/**
 * Created by PhpStorm.
 * User: EDZ
 * Date: 2019/5/27
 * Time: 11:46
 */
namespace wxpay;
require_once "lib/WxPay.Api.php";
class Wxpay extends \WxPayApi{

    /*
     *企业付款到零钱
     *appid小程序id    mch_id 商户号id    openid      amount 金额   ip 服务器ip secret
     *
     * */
    public function transfers($params){
        $url="https://api.mch.weixin.qq.com/mmpaymkttransfers/promotion/transfers";
        $data=[];
        $data['mch_appid']=$params['appid'];//小程序id
        $data['mch_id']=$params['mch_id'];//商户号id
        $data['nonce_str']=$this->getNonceStr();//随机字符串
        $data['partner_trade_no']=date('YmdHis',time()).rand(1111,9999);//商户订单号
        $data['openid']=$params['openid'];//用户openid
        $data['check_name']='NO_CHECK';//不校验真实姓名
        $data['re_user_name']=$params['name'];//收款用户姓名
        $data['amount']=$params['amount'] * 100;//金额
        $data['desc']=$params['desc'];//备注
        $data['spbill_create_ip']=$params['ip'];//ip


        $data['sign']=$this->getSign($data,$params['secret']);

        $data=$this->arraytoxml($data);
        $http=new \fast\Http;
        $res=$http::sendRequest($url,$data);
//        $res=$this->curl_post_ssl($url,$data);
        $res=$this->xmltoarray($res['msg']);
        return $res;
    }

    public function getSign($data,$secret){
        ksort($data);
        $str='';
        foreach($data as $k=>$v){
            $str.=$k.'='.$v.'&';
        }
        $str.='key='.$secret;
        $sign=md5($str);
        return $sign;
    }


    //遍历数组方法
    public function arraytoxml($data){
         $str='<xml>';
         foreach($data as $k=>$v) {
             $str.='<'.$k.'>'.$v.'</'.$k.'>';
         }
         $str.='</xml>';
         return $str;
        }


    public function xmltoarray($xml) {
     //禁止引用外部xml实体 
     libxml_disable_entity_loader(true);
     $xmlstring = simplexml_load_string($xml, 'SimpleXMLElement', LIBXML_NOCDATA);
     $val = json_decode(json_encode($xmlstring),true);
     return $val;
    }

    function curl_post_ssl($url,$param="") {

        $postUrl = $url;
        $curlPost = $param;
        $ch = curl_init();                                      //初始化curl
        curl_setopt($ch, CURLOPT_URL,$postUrl);                 //抓取指定网页
        curl_setopt($ch, CURLOPT_HEADER, 0);                    //设置header
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);            //要求结果为字符串且输出到屏幕上
        curl_setopt($ch, CURLOPT_POST, 1);                      //post提交方式
        curl_setopt($ch, CURLOPT_POSTFIELDS, $curlPost);           // 增加 HTTP Header（头）里的字段
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);        // 终止从服务端进行验证
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);
        curl_setopt($ch,CURLOPT_SSLCERT,ROOT_PATH .'/cert/apiclient_cert.pem'); //这个是证书的位置绝对路径
        curl_setopt($ch,CURLOPT_SSLKEY,ROOT_PATH .'/cert/apiclient_key.pem'); //这个也是证书的位置绝对路径
        $data = curl_exec($ch);                                 //运行curl
        curl_close($ch);
        return $data;
    }











}