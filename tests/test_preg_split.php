<?php
$keywords = preg_split("/[\s,]+/", "hypertext language, programming");
print_r($keywords);

$str = 'string';
$chars = preg_split('//', $str, -1, PREG_SPLIT_NO_EMPTY);
print_r($chars);

$str = 'hypertext language programming';
$chars = preg_split('/ /', $str, -1, PREG_SPLIT_OFFSET_CAPTURE);
print_r($chars);

$content = '<strong>Lorem ipsum dolor</strong> sit <img src="test.png" />amet <span class="test" style="color:red">consec<i>tet</i>uer</span>.';
$chars = preg_split('/<[^>]*[^\/]>/i', $content, -1, PREG_SPLIT_NO_EMPTY | PREG_SPLIT_DELIM_CAPTURE);
print_r($chars);

$content = '["洪","绿","蓝"]';
$chars = preg_split('/\["|","|"\]/', $content, -1, PREG_SPLIT_NO_EMPTY | PREG_SPLIT_DELIM_CAPTURE);
print_r($chars);


function to_spec($spec_array) {
    $content = implode('","',$spec_array);
    return '["' . $content. '"]';
};

print_r(to_spec($chars));

$content = '';
$chars = preg_split('/\["|","|"\]/', $content, -1, PREG_SPLIT_NO_EMPTY | PREG_SPLIT_DELIM_CAPTURE);
print_r($chars);
