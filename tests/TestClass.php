<?php  
/** 
 * A test class  在此处不能添加@ur,@param,@return 注释 
 *  如果要将类的注释和方法的注释合并的话，添加了上面的注释，会将方法中的注释给覆盖掉 
 */  
class TestClass {  
    /** 
     * @desc 获取public方法 
     * 
     * @url GET pnrs 
     * @param array $request_data 
     * @return int id 
     */  
    public function getPublicMethod($no_default,$add_time = '0000-00-00') {  
        echo "public";  
    }  
    /** 
     * @desc 获取private方法 
     * 
     * @url GET private_test 
     * @return int id 
     */  
    private function getPrivateMethod($no_default,$time = '0000-00-00') {  
        echo "private";  
    }  
  
    /** 
     * @desc 获取protected方法 
     * 
     * @url GET protected_test 
     * @param $no_defalut,$time 
     * @return int id 
     */  
    protected function getProtectedMethod($no_default,$time = '0000-00-00') {  
        echo "protected";  
    }  
}  