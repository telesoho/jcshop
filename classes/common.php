<?php
require_once __DIR__ . '/../plugins/vendor/autoload.php';
use Endroid\QrCode\QrCode;
use Monolog\Logger;
use Monolog\Handler\StreamHandler;
/**
 * @brief 公共方法集合
 * @class Common
 * @note  公开方法集合适用于整个系统
 */
class Common{


    /**
     * 记录用户操作
     * @param int $type
     * @param int $did
     * @return bool
     */
    public static function record($type = 0, $did = 0){
        /* 获取当前用户 */
        $user_id = IWeb::$app->getController()->user['user_id'];
        if(empty($type) || empty($did) || empty($user_id)) return false;
        /* 记录操作 */
        $model = new IModel('record');
        $model->setData(array('user_id' => $user_id, 'type' => $type, 'did' => $did, 'create_time' => time()));
        $rel = $model->add();
        return $rel>0 ? true : false;
    }

    /**
     * 调试-数据库写入
     * @param $data
     */
    public static function dblog($data){
        //数据库
        $data  = json_encode($data);
        $model = new IModel('log_debug');
        $model->setData(array('msg' => $data, 'date' => date('Y-m-d H:i:s', time())));
        return $model->add();
    }

    /**
     * @brief 获取评价分数
     * @param $grade float 分数
     * @param $comments int 评论次数
     * @return float
     */
    public static function gradeWidth($grade, $comments = 1){
        return $comments==0 ? 0 : round($grade/$comments);
    }

    /**
     * @brief 获取用户状态
     * @param $status int 状态代码
     * @return string
     */
    public static function userStatusText($status){
        $mapping = array('1' => '正常', '2' => '删除', '3' => '锁定');
        return isset($mapping[$status]) ? $mapping[$status] : '';
    }

    /**
     * 获取本地版本信息
     * @return String
     */
    public static function getLocalVersion(){
        return include(IWeb::$app->getBasePath().'docs/version.php');
    }
    /**
     * User: chenbo
     * 日志写
     * @param $info
     */
    public static function log_write($info, $type='INFO'){
        $log = new Logger('debugger');
        $log_path = __DIR__ . '/../backup/logs/b2b2c';
        if (!file_exists($log_path)) mkdir($log_path);
        switch ($type){
            case 'DEBUG':
                $log->pushHandler(new StreamHandler($log_path . '/'.date('Y-m-d').'-DEBUG.log', Logger::DEBUG));
                $log->addInfo($info);
            case 'INFO':
                $log->pushHandler(new StreamHandler($log_path . '/'.date('Y-m-d').'-INFO.log', Logger::INFO));
                $log->addInfo($info);
            default:
                $log->pushHandler(new StreamHandler($log_path . '/'.date('Y-m-d').'-WARNING.log', Logger::WARNING));
                $log->addInfo($info);
        }
    }
    /**
     * User: chenbo
     * post请求
     * @param $url
     * @param $jsonStr
     * @return array
     */
    public static function http_post_json($url, $jsonStr)
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $jsonStr);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
                'Content-Type: application/json; charset=utf-8',
                'Content-Length: ' . strlen($jsonStr)
            )
        );
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

        return array($httpCode, $response);
    }
    /**
     * User: chenbo
     * 获取用户access_token
     * @return mixed
     */
    public static function get_wechat_access_token(){
        require_once __DIR__.'/../plugins/wechat/wechat.php';
        $wechat = new wechat();
        $access_token = $wechat->getAccessToken();
        return $access_token;
    }
    /**
     * User: chenbo
     * 添加用户扫码关注
     */
    public static function add_qrcode_follow($scene_id,$open_id){
        if (!empty($scene_id)){
            $follow_query = new IQuery('follow');
            $follow_query->where = 'open_id = "' . $open_id.'"';
            $follow_data = $follow_query->find();
            if (!empty($follow_data)){
                $follow_model = new IModel('follow');
                $follow_model->setData(['scene_id'=>$scene_id ,'follow_time'=>date('Y-m-d H:i:s',time())]);
                $follow_model->update('open_id = "' . $open_id . '"');
            } else {
                $follow_model = new IModel('follow');
                $follow_model->setData(['scene_id'=>$scene_id, 'open_id'=>$open_id ,'follow_time'=>date('Y-m-d H:i:s',time())]);
                $follow_model->add();
            }
        } else {
            $follow_model = new IModel('follow');
            $follow_model->setData(['unsubscribe_time'=>date('Y-m-d H:i:s',time())]);
            $follow_model->update('open_id="'.$open_id.'"');
        }
    }

    public static function jssdk(){
        if(IClient::isWechat() == true){
            require_once __DIR__ . '/../plugins/wechat/wechat.php';
            $wechat = new wechat();
            $wechat->setConfig();
            $wechat->config['wechat_jsApiSDK'] = 1;
            $wechat->jsApiSDK();
            return true;
        } else {
            return false;
        }
    }
}