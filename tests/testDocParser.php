<?php  
require_once dirname(__FILE__).'/DocParser.php';  
require_once dirname(__FILE__).'/TestClass.php';  

/** 
 * 解析doc 
 * 下面的DocParserFactory是对其的进一步封装，每次解析时，可以减少初始化DocParser的次数 
 * 
 * @param $php_doc_comment 
 * @return array 
 */  
function parse_doc($php_doc_comment) {  
    $p = new DocParser ();  
    return $p->parse ( $php_doc_comment );  
}  
  
/** 
 * Class DocParserFactory 解析doc 
 * 
 * @example 
 *      DocParserFactory::getInstance()->parse($doc); 
 */  
class DocParserFactory{  
  
    private static $p;  
    private function DocParserFactory(){  
    }  
  
    public static function getInstance(){  
        if(self::$p == null){  
            self::$p = new DocParser ();  
        }  
        return self::$p;  
    }  
}  

$class_name = 'TestClass';  
  
$reflection = new ReflectionClass ( $class_name );  
//通过反射获取类的注释  
$doc = $reflection->getDocComment ();  
//解析类的注释头  
$parase_result =  DocParserFactory::getInstance()->parse ( $doc );  
$class_metadata = $parase_result;  
  
//输出测试  
// var_dump ( $doc );  
echo "\r\n";  
print_r( $parase_result );  
echo "\r\n-----------------------------------\r\n";  

//获取类中的方法，设置获取public,protected类型方法  
$methods = $reflection->getMethods(ReflectionMethod::IS_PUBLIC + ReflectionMethod::IS_PROTECTED + ReflectionMethod::IS_PRIVATE);  
//遍历所有的方法  
foreach ($methods as $method) {  
    //获取方法的注释  
    $doc = $method->getDocComment();  
    //解析注释  
    $info = DocParserFactory::getInstance()->parse($doc);  
    $metadata = $class_metadata +  $info;  
    //获取方法的类型  
    $method_flag = $method->isProtected();//还可能是public,protected类型的  
    //获取方法的参数  
    $params = $method->getParameters();  
    $position=0;    //记录参数的次序  
    foreach ($params as $param){  
        $arguments[$param->getName()] = $position;  
        //参数是否设置了默认参数，如果设置了，则获取其默认值  
        $defaults[$position] = $param->isDefaultValueAvailable() ? $param->getDefaultValue() : NULL;  
        $position++;  
    }  
  
    $call = array(  
        'class_name'=>$class_name,  
        'method_name'=>$method->getName(),  
        'arguments'=>$arguments,  
        'defaults'=>$defaults,  
        'metadata'=>$metadata,  
        'method_flag'=>$method_flag  
    );  
    print_r($call);  
    echo "\r\n-----------------------------------\r\n";  
} 