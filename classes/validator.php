<?php

/**
 * @brief 验证器
 * @class validator
 * @auth twh
 */

class Validator {

    // 验证器声明
    private $validator_def = array(
        "size" => array('name' => 'Validator::v_size','desc' => '字符串长度验证'),
        "min" => array('name' => 'Validator::v_min','desc' => '最小值验证'),
        "max" => array('name' => 'Validator::v_max','desc' => '最大值验证'),
        "int" => array('name' => 'Validator::v_int','desc' => '整数验证'),
        "number" => array('name' => 'Validator::v_number','desc' => '数值验证'),
        "string" => array('name' => 'Validator::v_string','desc' => '字符串验证'),
        "datetime" => array('name' => 'Validator::v_datetime','desc' => '日期验证'),
        "email" => array('name' => 'Validator::v_email','desc' => '电子邮件格式验证'),
        "qq" => array('name' => 'Validator::v_qq','desc' => 'QQ号码验证'),
        "idcard" => array('name' => 'Validator::v_idcard','desc' => '身份证验证包括一二代身份证'),
        "ip" => array('name' => 'Validator::v_ip','desc' => 'IPV4验证'),
        "zip" => array('name' => 'Validator::v_zip','desc' => '邮政编码验证'),
        "phone" => array('name' => 'Validator::v_phone','desc' => '电话号码验证'),
        "mobile" => array('name' => 'Validator::v_mobile','desc' => '手机号码验证'),
        "url" => array('name' => 'Validator::v_url','desc' => 'Url地址验证'),
        "check" => array('name' => 'Validator::v_check','desc' => '正则验证'),
        "required" => array('name' => 'Validator::v_required','desc' => '是否为空验证'),
        "percent" => array('name' => 'Validator::v_percent','desc' => '百分比数字验证'),
        "username" => array('name' => 'Validator::v_username','desc' => '用户名验证'),
        "filename" => array('name' => 'Validator::v_filename','desc' => '文件名或者文件路径验证'),
        "strict" => array('name' => 'Validator::v_strict','desc' => '常用检索过滤'),
    );

    // 错误信息
    private $errMsg = array();

    // 返回验证的错误信息
    public function getErrMsg() {
        return $this->errMsg;
    }

    // 解析验证器
    private function parseValidator($vStr) {
        $re = '/(\w+):?(.*)/';
        if(preg_match($re, $vStr, $m)) {
            $funKey = $m[1];
            $param = $m[2];
            return array($this->validator_def[$funKey], $param);
        } else {
            return array($vStr, "");
        }
    }


    /**
     * 校验输入结果并返回实际的数组
     * @param array $input 需要校验的数组
     * @param array $validators 检验定义数组
     * @return 返回校验后的结果数组
     * @throw Exception 校验失败则抛出异常
     */
    public function validate_array($inputs, $validators){
        $this->errMsg = array();
        foreach($validators as $key => $vs) {
            foreach($vs as $v) {
                if($v === 'isset') {
                    if(!isset($inputs[$key])) {
                        $this->errMsg[] = $key."索引不存在";
                    }
                } else {
                    if(isset($inputs[$key])) {
                        // 对象存在
                        list($fun_def, $param) = $this->parseValidator($v);
                        if(!call_user_func(array(&$this,$fun_def['name']), $inputs[$key], $param)){
                            $this->errMsg[] = $key . $fun_def['desc'] . $param . "失败";
                            break;
                        }
                    }
                }
            }
        }
        return $this->errMsg?false:true;
    }

    
    public function validate_obj($input, $validators) {
        foreach($validators as $v) {
            list($fun_def, $param) = $this->parseValidator($v);
            if(!call_user_func($fun_def['name'], $input, $param)){
                $this->errMsg[] =  $fun_def['desc'] . $param . "失败";
                break;
            }
        }
        return true;
    }

	/**
	 * @brief 按指定格式验证日期字符串是否是正确的日期
	 * @param string $date 自定义格式的日期
	 * @param string $format 日期格式 
	 * IFilter::isValidDateTime("2012-12-21", "Y-m-d");
	 */
    static function v_datetime($val, $param) {
		$dateInfo = date_parse_from_format($param, $val);
		return (bool)($dateInfo['error_count'] == 0);
    }

    static public function v_int($val, $param) {
        return is_int($val);
    }

    static public function v_min($val, $param) {
        $min = floatval($param);
        return $val >= $min;
    }

    static public function v_max($val, $param) {
        $max = floatval($param);
        return $val <= $max;
    }

    static public function v_string($val, $param) {
        return is_string($val);
    }

    static public function v_number($val, $param) {
        return is_float($val) || is_int($val);
    }

    static public function v_size($val, $param) {
        $re = '/\[([-+]?\d*)-([-+]?\d*)\]/';
        if(!preg_match($re, $param, $m))
        {
            return false;
        }

        $len = strlen($val);
        $minlen = intval($m[1]);
        $maxlen = intval($m[2]);

        if($m[1] == '') {
            return $len <= $maxlen;
        }

        if($m[2] == '') {
            return $len >= $minlen;
        }

        return ($len >= $minlen && $len < $maxlen);
    }

    /**
     * @brief Email格式验证
     * @param string $str 需要验证的字符串
     * @return bool 验证通过返回 true 不通过返回 false
     */
    public static function v_email($str, $param)
    {
        return (bool)preg_match('/^\w+([-+.]\w+)*@\w+([-.]\w+)+$/i',$str);
    }
    /**
     * @brief QQ号码验证
     * @param string $str 需要验证的字符串
     * @return bool 验证通过返回 true 不通过返回 false
     */
    public static function v_qq($str, $param)
    {
        return (bool)preg_match('/^[1-9][0-9]{4,}$/i',$str);
    }
    /**
     * @brief 身份证验证包括一二代身份证
     * @param string $str 需要验证的字符串
     * @return bool 验证通过返回 true 不通过返回 false
     */
    public static function v_idcard($str, $param)
    {
        return (bool)preg_match('/^\d{15}(\d{2}[0-9x])?$/i',$str);
    }
    /**
     * @brief 此IP验证只是对IPV4进行验证。
     * @param string $str 需要验证的字符串
     * @return bool 验证通过返回 true 不通过返回 false
     * @note IPV6暂时不支持。
     */
    public static function v_ip($str, $param)
    {
        return (bool)preg_match('/^(([0-9]|[1-9][0-9]|1[0-9]{2}|2[0-4][0-9]|25[0-5])\.){3}([0-9]|[1-9][0-9]|1[0-9]{2}|2[0-4][0-9]|25[0-5])$/i',$str);
    }
    /**
     * @brief 邮政编码验证
     * @param string $str 需要验证的字符串
     * @return bool 验证通过返回 true 不通过返回 false
     * @note 此邮编验证只适合中国
     */
    public static function v_zip($str, $param)
    {
        return (bool)preg_match('/^\d{6}$/i',$str);
    }

    /**
     * @brief 电话号码验证
     * @param string $str 需要验证的字符串
     * @return  bool 验证通过返回 true 不通过返回 false
     */
    public static function v_phone($str,$param)
    {
        return (bool)preg_match('/^((\d{3,4})|\d{3,4}-)?\d{3,8}(-\d+)*$/i',$str);
    }
    /**
     * @brief 手机号码验证
     * @param string $str
     * @return  bool 验证通过返回 true 不通过返回 false
     */
    public static function v_mobile($str, $param)
    {
		return (bool)preg_match("!^1[3|4|5|7|8][0-9]\d{4,8}$!",$str);
    }
    /**
     * @brief Url地址验证
     * @param string $str 要检测的Url地址字符串
     * @return bool 验证通过返回 true 不通过返回 false
     */
    public static function v_url($str, $param)
    {
        return (bool)preg_match('/^[a-zA-z]+:\/\/(\w+(-\w+)*)(\.(\w+(-\w+)*))+(\/?\S*)?$/i',$str);
    }
    /**
     * @brief 正则验证接口
     * @param mixed $reg 正则表达式
     * @param string $str 需要验证的字符串
     * @return bool 验证通过返回 true 不通过返回 false
     */
    public static function v_check($str, $reg)
    {
        return (bool)preg_match('/^'.$reg.'$/i',$str);
    }
	/**
     * @brief 判断字符串是否为空
     * @param string $str 需要验证的字符串
     * @return bool 验证通过返回 true 不通过返回 false
     */
    public static function v_required($str, $param)
    {
         return (bool)preg_match('/\S+/i',$str);
    }

	/**
     * @brief 百分比数字
     * @param string $str 需要验证的字符串
     * @return bool 验证通过返回 true 不通过返回 false
     */
    public static function v_percent($str, $param)
    {
    	return (bool)preg_match('/^[1-9][0-9]*$/',$str);
    }

	/**
     * @brief 用户名
     * @param string $str 需要验证的字符串
     * @return bool 验证通过返回 true 不通过返回 false
     */
    public static function v_username($str, $param)
    {
        $re = '/\[([-+]?\d*)-([-+]?\d*)\]/';
        $minlen = 2;
        $maxlen = 40;
        if($param)
        {
            if(preg_match($re, $param, $m))
            {
                $minlen = intval($m[1]);
                $maxlen = intval($m[2]);
            } else {
                return false;
            }
        }

		return (bool)preg_match("!^[\w\x{4e00}-\x{9fa5}]{".$minlen.",".$maxlen."}$!u",$str);
    }

	/**
     * @brief 文件名或者文件路径
     * @param string $str 需要验证的字符串
     * @return bool 验证通过返回 true 不通过返回 false
     */
    public static function v_filename($str,$param)
    {
		return (bool)preg_match("%^[\w\./]+$%",$str);
    }

	/**
     * @brief 常用检索过滤
     * @param string $str 需要验证的字符串
     * @return bool 验证通过返回 true 不通过返回 false
     */
    public static function v_strict($str, $param)
    {
    	return (bool)preg_match("|^[\w\.\-<>=\!\x{4e00}-\x{9fa5}\s*]+$|u",$str);
    }

}

/*
$v = new Validator();

$validators = array(
    "OrderNo" => array('isset','size:[1-40]'),
    "OrderTime" => array('isset','datetime:YmdHis'), 
    "GoodsPrice" => array('number','min:1','max:20'), 
    "Email" => array('email'), 
    "Username" => array('username:[2-8]'), 
    "mobile"  => array('mobile'),
);

$val = array(
    'OrderNo' => 'abc',
    'OrderTime' => '20121221121212',
    "GoodsPrice" => 22.23,
    "Email" => "afdafd@gmail.com",
    "Username" => "1234568",
    "mobile" => "13667787828",
);

// print _validate(14, array('int', 'min:3', 'max:12'));
// print $v->_validate("9adf", array('string', 'ranglen:[3,10]'));
if(!$v->validate_array($val, $validators)){
    print_r($v->getErrMsg());
}
*/