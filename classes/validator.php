<?php
/**
 * @copyright Copyright(c) 2017 jiumaojia.com
 * @file
 * @brief 输入验证类文件
 * @author twh
 * @date 2017-02-07
 * @version 0.1
 * @note
 */
/**
 * @brief 输入验证类文件
 * @class validator
 */
class Validator
{
    private $errMsg = array();

    function __construct($config = array()){
    }

    /**
     * 校验输入结果并返回实际的数组
     * @param array $inputs 需要校验的数组
     * @param array $validators 检验定义数组
     * @return 返回校验后的结果数组
     * @throw Exception 校验失败则抛出异常
     */
    public function validate($input, $validators) {
        if(is_array($input)) {
            return $this->_validate_array($inputs, $validators);
        } elseif(is_string($input)) {
            return $this->_validate($input, $validators);
        }
        return $input;
    }

    public function getErrMsg() {
        return $this->errMsg;
    }

    private function _validate_array($inputs, $validators){
        $outputs = $inputs;
        
        foreach($validators as $key => $v) {
            if(!isset($input[$key])) {
                $$this->errMsg[] = "$key变量不存在";
            }
            // $this->_validate($val, $validators[$key]);
        }
        return $outputs;
    }

    /**
     * 校验
     * $this->_validate(33, array('int', 'min:3', 'max:333'))
     * $this->_validate('abcdef', array('string', 'ranglen:[1,30]'))
     */
    function _validate($input, $validators) {
        foreach($validators as $validator) {
            list($fun, $param) = explode(":", $validator);
            $fun = 'v_'. $fun;
            if(!call_user_func(array(&$this, $fun), $input, $param)) {
                $this->errMsg[] = "$fun:$param 校验失败";
                return false;
            }
        }
        return true;
    }

    // int
    public function v_int($val, $param) {
        return is_int($val);
    }

    // min:2
    public function v_min($val, $param) {
        $min = floatval($param);
        return $val >= $min;
    }

    // max:23
    public function v_max($val, $param) {
        $max = floatval($param);
        return $val <= $max;
    }

    // string
    public function v_string($val, $param) {
        return is_string($val);
    }

    // 'ranglen:[23,32]'
    public function v_ranglen($val, $param) {
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
