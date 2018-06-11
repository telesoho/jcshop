<?php
require_once  __DIR__ . '/../plugins/vendor/autoload.php';
use Curl\Curl;
use Monolog\Logger;
use Monolog\Handler\StreamHandler;
use Monolog\Formatter\LineFormatter;

/**
 * author: telesoho
 */
class xloboapi
{
    private static $beInit = false;

    public static $config = array (
        'api_server'        =>  "http://114.80.87.216:8082/api/router/rest",
        'access_token'      =>  "ACiYUZ6aKC48faYFD6MpvbOf73BdE9OV5g15q1A6Ghs+i/XIawq/9RHJCzc6Y3UNxA==",
        'client_secret'     =>  "APvYM8Mt5Xg1QYvker67VplTPQRx28Qt/XPdY9D7TUhaO3vgFWQ71CRZ/sLZYrn97w==",
        'client_id'         =>  "68993573-E38D-4A8A-A263-055C401F9369",
        'version'           => '1.0',
        "log_level"         => Logger::INFO
    );

    public static $log ;

    public static function config() {
        return self::$config;
    }

    /**
     * 初始化函数
     * @param $config
     */
    public static function init($config = null)
    {
        if ($config) {
            self::$config = $config;
        }

        // 设置输出日志
        self::$log = new Logger('xlobo_open_api');
        self::$log->useMicrosecondTimestamps(true);
        $log_path = __DIR__ . '/../backup/logs/xlobo_open_api';
        if (!file_exists($log_path)) mkdir($log_path);
        
        $dateFormat = "Y-m-d H:i:s.u";
        $output = "[%datetime%] %level_name%:%message% %context%\n";
        $formatter = new LineFormatter($output, $dateFormat);
        
        $stream = new StreamHandler($log_path . '/'.date('Y-m-d').'.log', $config['log_level']);
        $stream->setFormatter($formatter);
        self::$log->pushHandler($stream);

        self::$beInit = true;
    }

    public static function requests($method, $data) 
    {
        if (!self::$beInit) {
            self::init();
        }

        $sign = self::sign($data);
        $url  = self::$config['api_server'];
        $curl = new \Curl\Curl();
        $params = array(
            'method'       => $method,
            'v'            => self::$config['version'],
            'msg_param'    => json_encode($data),
            'client_id'    => self::$config['client_id'],
            'sign'         => $sign,
            'access_token' => self::$config['access_token']
        );
        $curl->setHeader('Content-Type', 'application/x-www-form-urlencoded; charset=GBK');
        $ret = $curl->post($url, $params);
        if ($ret === false) {
            self::$log->error(print_r($ret, true));
            self::$log->error(print_r($params,true));
            throw new Exception('服务器未响应'.self::$config['api_server']);
        } else if (isset($ret->ErrorCode)){
            self::$log->error(print_r($ret, true));
            self::$log->error(print_r($params,true));
            throw new Exception(print_r($ret, true) . print_r($params, true));
        }

        self::$log->debug($url, $params, $ret);
        return $ret;
    }

    /**
     * 数字签名签名
     */
    private static function sign($data)
    {
        $json_data = json_encode($data);
        $client_secret = self::$config['client_secret'];
        $content = strtolower($client_secret . $json_data . $client_secret);
        self::$log->debug("content", array($content));
        $sign = md5(base64_encode($content));
        self::$log->debug("sign", array($sign));
        return $sign;
    }
};