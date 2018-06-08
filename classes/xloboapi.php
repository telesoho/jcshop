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
        'sign'              => '70da2949ce84f7d9fb0297cd33ee6ae6',

        // 是否输出调试信息
        "debug"             => "false"
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
        
        $stream = new StreamHandler($log_path . '/'.date('Y-m-d').'.log');
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
            'sign'         => self::$config['sign'],
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

        if(self::$config['debug'] == 'true') {
            self::$log->debug($url, $params, $ret);
        }
        return $ret;
    }

    /**
     * 数字签名签名
     */
    private static function sign($data)
    {
        $content = strtolower(self::$config['sign'] . json_encode($data) . self::$config['sign']);
        self::$log->info($content);
        $sign = md5(base64_encode($content));
        return $sign;
    }
};