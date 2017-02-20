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
    static function send_message_template($open_id, $type, $send_info, $log_info = ''){
        $access_token      = common::get_wechat_access_token();
        if ($access_token === false){
            common::log_write("消息推送-失败:$open_id,获取access_token失败,日志文件夹access_token ERROR", 'INFO', 'send_message');
            return false;
        }
        $url               = 'https://api.weixin.qq.com/cgi-bin/message/template/send?access_token=' . $access_token;
        $site_config       = new Config('site_config');
        $site_config_array = $site_config->getInfo();
        $template_array    = $site_config_array['wechat_template_array'];
        switch ($type){
            //优惠券
            case 'coupon':
                $params = sprintf('{
                   "touser":"%s",
                   "template_id":"%s",
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
               }',$open_id,$template_array[$type],IUrl::getHost(),$send_info['quantity'],$send_info['certificateNumbers'],'优惠券的有效期时间：自' . date('Y-m-d H:i:s') . '起7天之内');
                break;
            case 'shop':
                $params = sprintf('{
                   "touser":"%s",
                   "template_id":"%s",
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
               }',$open_id,$template_array[$type],IUrl::getHost().'/site/index/rrd/'.$send_info['recommender_id']);
                break;
            case 'sfz':
                $params = sprintf('{
                   "touser":"%s",
                   "template_id":"%s",
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
               }',$open_id,$template_array[$type],IUrl::getHost(),$send_info['order_no'],$send_info['accept_name'] . '___' . $send_info['mobile'] . '-->' . $send_info['sfz_name'] . '___' . $send_info['mobile'], $send_info['address']);
                break;
            case 'shiming':
                $params = sprintf('{
                   "touser":"%s",
                   "template_id":"%s",
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
               }',$open_id,$template_array[$type],IUrl::getHost().'/simple/credit',$send_info['order_no']);
                break;
            case 'order_complete':
                $params = sprintf('{
                   "touser":"%s",
                   "template_id":"%s",
                   "url":"%s",            
                   "data":{
                           "first": {
                               "value":"订单支付成功",
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
                           "keyword3":{
                               "value":"%s",
                               "color":"#173177"
                           },
                           "remark": {
                               "value":"订单支付成功。您的宝贝很快就会飞过来咯！",
                               "color":"#173177"
                           }
                   }
               }',$open_id,$template_array[$type],IUrl::getHost().'/ucenter/index',date('Y-m-d H:i:s',time()),$send_info['goods_name'],$send_info['order_no']);
                break;
            case 'receive':
                $params = sprintf('{
                   "touser":"%s",
                   "template_id":"%s",
                   "url":"%s",            
                   "data":{
                           "first": {
                               "value":"优惠券礼品录取成功通知",
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
                           "keyword3":{
                               "value":"%s",
                               "color":"#173177"
                           },
                           "remark": {
                               "value":"优惠券领取成功，在《个人中心》->《我的优惠券》中查看优惠券",
                               "color":"#173177"
                           }
                   }
               }',$open_id,$template_array[$type],IUrl::getHost().'/site/ticket_list',$send_info['username'],$send_info['ticket_name'],date('Y-m-d H:i:s',time()));
                break;
            case 'ship':
                $params = sprintf('{
                   "touser":"%s",
                   "template_id":"%s",
                   "url":"%s",            
                   "data":{
                           "first": {
                               "value":"发货通知",
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
                           "keyword3":{
                               "value":"%s",
                               "color":"#173177"
                           },
                           "remark": {
                               "value":"喵~感谢您对九猫家的信任与支持！我们已经收到您的订单啦~ 日本供货商将在3-5个工作日完成配货哒，正常情况下10-15个工作日您将收到您买的宝贝，请耐心等待哦ฅ՞•ﻌ•՞ฅ~\n如果有任何订单退换货等问题请添加客服喵微信：\njiumaojia006；想要领取优惠券的小伙伴欢迎添加喵酱个人微信：jiumaojia001；更多优惠群里第一时间共享哦~么么哒~",
                               "color":"#173177"
                           }
                   }
               }',$open_id,$template_array[$type],IUrl::getHost().'/site/index',$send_info['order_no'],$send_info['name'],$send_info['billcode']);
                break;
            case 'member':
                if ($send_info['remark_goods_id']){
                    $url_info = IUrl::getHost()."/site/products/id/".$send_info['remark_goods_id'].'/t/1';
                } else {
                    $url_info = IUrl::getHost()."/site/index";
                }
                $params = sprintf('{
                   "touser":"%s",
                   "template_id":"%s",
                   "url":"%s",            
                   "data":{
                           "first": {
                               "value":"喵~欢迎成为九猫家的一份子~",
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
                               "value":"%s",
                               "color":"#173177"
                           }
                   }
               }',$open_id,$template_array[$type],$url_info,$send_info['number'],$send_info['create_time'], $send_info['remark']);
                break;
            case 'member2':
                $url_info = IUrl::getHost()."/site/products/id/".$send_info['remark_goods_id'].'/t/1';
                $params = sprintf('{
                   "touser":"%s",
                   "template_id":"%s",
                   "url":"%s",            
                   "data":{
                           "first": {
                               "value":"喵~ 九猫家限时抢快开始啦！",
                               "color":"#173177"
                           },
                           "keyword1":{
                               "value":"%s",
                               "color":"#173177"
                           },
                           "keyword2":{
                               "value":"九猫家限时抢",
                               "color":"#173177"
                           },
                           "keyword3":{
                               "value":"%s",
                               "color":"#173177"
                           },
                           "keyword4":{
                               "value":"戳下方详情~",
                               "color":"#173177"
                           },
                           "remark": {
                               "value":"%s",
                               "color":"#173177"
                           }
                   }
               }',$open_id,$template_array[$type],$url_info,$send_info['username'],$send_info['time'], $send_info['remark']);
                break;
            case 'tip_coupon_expires':
                $params = sprintf('{
                   "touser":"%s",
                   "template_id":"%s",
                   "url":"%s",            
                   "data":{
                           "first": {
                               "value":"%s将要过期",
                               "color":"#173177"
                           },
                           "keyword1":{
                               "value":"优惠券的使用",
                               "color":"#173177"
                           },
                           "keyword2":{
                               "value":"%s",
                               "color":"#173177"
                           },
                           "remark": {
                               "value":"点击查看详情",
                               "color":"#173177"
                           }
                   }
               }',$open_id, $template_array[$type], IUrl::getHost().'/site/ticket_list',$send_info['coupon_name'],$send_info['end_time']);
                break;
            case 'project':
                $params = sprintf('{
                   "touser":"%s",
                   "template_id":"%s",
                   "url":"%s",            
                   "data":{
                           "first": {
                               "value":"定时推送消息情况",
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
                               "value":"%s",
                               "color":"#173177"
                           }
                   }
               }',$open_id, $template_array[$type], IUrl::getHost(),$send_info['type'],$send_info['time'],$send_info['info']);
                break;
        }
        $ret = common::http_post_json($url,$params);
        if (json_decode($ret[1])->errcode === 0){
            common::log_write("$log_info 消息推送-成功:$open_id " . print_r($ret,true), 'INFO', 'send_message');
            return true;
        } else {
            if (isset(json_decode($ret[1])->errmsg)){
                common::log_write("$log_info 消息推送-".json_decode($ret[1])->errmsg."失败:$open_id " . print_r($ret,true), 'INFO', 'send_message');
            } else {
                common::log_write("$log_info 消息推送-失败:$open_id " . print_r($ret,true), 'INFO', 'send_message');
            }
            return false;
        }
    }
}