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
        $re = '/\[([-+]?\d*),([-+]?\d*)\]/';
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

}


$v = new Validator();

$validators = array(
    "OrderNo" => array('isset','size:[1-40]'),
    "OrderTime" => array('isset','datetime:YmdHis'), 
    "GoodsPrice" => array('number','min:1','max:20'), 
);

$val = array(
    'OrderNo' => 'abc',
    'OrderTime' => '20121221121212',
    "GoodsPrice" => 22.23,
);

// print _validate(14, array('int', 'min:3', 'max:12'));
// print $v->_validate("9adf", array('string', 'ranglen:[3,10]'));
if(!$v->validate_array($val, $validators)){
    print_r($v->getErrMsg());
}
