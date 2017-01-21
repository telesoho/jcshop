<?php
require_once __DIR__ . '/../plugins/vendor/autoload.php';
use Endroid\QrCode\QrCode;
use Monolog\Logger;
use Monolog\Handler\StreamHandler;

/**
 * Created by PhpStorm.
 * User: chenbo
 * Date: 2016/12/22
 * Time: 15:37
 */
class wechats
{
    /**
     * User: chenbo
     * 获取合伙人的二维码
     * @param $recommender_id
     * @return string
     */
    static function get_recommender_qrcode($recommender_id){
        if(IClient::isWechat()==true){
            $wxqrcode_path = __DIR__ . '/../upload/wxqrcode/recommender';
            if (!file_exists($wxqrcode_path)){
                mkdir(__DIR__ . '/../upload/wxqrcode');
                mkdir($wxqrcode_path);
            }
            if (!file_exists($wxqrcode_path . '/' . $recommender_id.'.png')){
                require_once __DIR__.'/../plugins/wechat/wechat.php';
                require_once __DIR__.'/../plugins/curl/Curl.php';
//                $wechat       = new wechat();
                $access_token = common::get_wechat_access_token();
                $curl         = new \Wenpeng\Curl\Curl();
                $url          = 'https://api.weixin.qq.com/cgi-bin/qrcode/create?access_token='.$access_token;
                $curl->post(json_encode(['action_name' => 'QR_LIMIT_SCENE', 'action_info' => ['scene' => ['scene_id' => $recommender_id]]]))->url($url);
                $ret = json_decode($curl->data());
                $qrcode_url = 'https://mp.weixin.qq.com/cgi-bin/showqrcode?ticket='.urlencode($ret->ticket);
                $img = file_get_contents($qrcode_url);
                file_put_contents($wxqrcode_path . '/' . $recommender_id.'.png',$img);
                common::log_write(print_r($ret,true));
            }
            return '/upload/wxqrcode/recommender/'.$recommender_id.'.png';
        }
    }

    /**
     * User: chenbo
     * 向用户推送模板消息
     * @param $open_id
     * @param $type
     * @param $send_info
     */
    static function send_message_template($open_id, $type, $send_info){
        $access_token = common::get_wechat_access_token();
        $url = 'https://api.weixin.qq.com/cgi-bin/message/template/send?access_token=' . $access_token;
        switch ($type){
            case 'coupon':
                $params = sprintf('{
                   "touser":"%s",
                   "template_id":"CdwoXX4wo7wjp0i73Jn8H7TQNVJEmOCV6eeI268wb-g",
                   "url":"%s",            
                   "data":{
                           "first": {
                               "value":"%s张邮费优惠券购买成功",
                               "color":"#173177"
                           },
                           "keyword1":{
                               "value":"邮费优惠券",
                               "color":"#173177"
                           },
                           "keyword2":{
                               "value":"%s",
                               "color":"#173177"
                           },
                           "keyword3":{
                               "value":"线上商城下单付款结算时输入该优惠券码使用",
                               "color":"#173177"
                           },
                           "remark": {
                               "value":"%s",
                               "color":"#173177"
                           }
                   }
               }',$open_id,IUrl::getHost(),$send_info['quantity'],$send_info['certificateNumbers'],'优惠券的有效期时间：自' . date('Y-m-d H:i:s') . '起7天之内');
                break;
            case 'shop':
                $params = sprintf('{
                   "touser":"%s",
                   "template_id":"jhVJZrhXm9quRXg-vSY6IVHDhZ69BywZb4SYblLAuXA",
                   "url":"%s",            
                   "data":{
                           "first": {
                               "value":"请您完善资料",
                               "color":"#173177"
                           },
                           "keyword1":{
                               "value":"九猫家商城",
                               "color":"#173177"
                           },
                           "keyword2":{
                               "value":"店铺信息",
                               "color":"#173177"
                           },
                           "keyword3":{
                               "value":"开店",
                               "color":"#173177"
                           },
                           "remark": {
                               "value":"谢谢您的配合",
                               "color":"#173177"
                           }
                   }
               }',$open_id,IUrl::getHost().'/site/index/rrd/'.$send_info['recommender_id']);
                break;
            case 'sfz':
                $params = sprintf('{
                   "touser":"%s",
                   "template_id":"lZaBFAn9GH0eOUUKBy7laf5_AbOMWIMUweTDIPXUS7c",
                   "url":"%s",            
                   "data":{
                           "first": {
                               "value":"订单%s收货人变动通知",
                               "color":"#173177"
                           },
                           "keyword1":{
                               "value":"%s",
                               "color":"#173177"
                           },
                           "keyword2":{
                               "value":"%s",
                               "color":"#173177"
                           },
                           "remark": {
                               "value":"由于您的收货人无实名认证信息，因此系统将订单收货人更改为微信用户实名认证的姓名，谢谢您的配合",
                               "color":"#173177"
                           }
                   }
               }',$open_id,IUrl::getHost(),$send_info['order_no'],$send_info['accept_name'] . '___' . $send_info['mobile'] . '-->' . $send_info['sfz_name'] . '___' . $send_info['mobile'], $send_info['address']);
                break;
            case 'shiming':
                $params = sprintf('{
                   "touser":"%s",
                   "template_id":"e4FVRqsTUHdGYCfPe4H8N81c7CLIds_PDOhzGpB3I2U",
                   "url":"%s",            
                   "data":{
                           "first": {
                               "value":"点击进入个人中心进行实名认证",
                               "color":"#173177"
                           },
                           "keyword1":{
                               "value":"未填写实名认证",
                               "color":"#173177"
                           },
                           "keyword2":{
                               "value":"2017-01-13",
                               "color":"#173177"
                           },
                           "remark": {
                               "value":"(订单:%s)由于您的收货人无实名认证信息，因此尽快填写实名认证的信息，订单收货人也将变更为该身份证用户的姓名，谢谢您的配合",
                               "color":"#173177"
                           }
                   }
               }',$open_id,IUrl::getHost().'/simple/credit',$send_info['order_no']);
                break;
        }
        $ret = common::http_post_json($url,$params);
        common::log_write(print_r($ret,true));
    }
}