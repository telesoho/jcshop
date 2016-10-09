<?php
return array(
	'logs'=>array(
		'path'=>'backup/logs',
		'type'=>'file'
	),
	'DB'=>array(
		'type'=>'mysqli',
        'tablePre'=>'iwebshop_',
		'read'=>array(
			array('host'=>'192.168.10.174:3306','user'=>'root','passwd'=>'123456','name'=>'test'),
		),

		'write'=>array(
			'host'=>'192.168.10.174:3306','user'=>'root','passwd'=>'123456','name'=>'test',
		),
	),
	'interceptor' => array('themeroute@onCreateController','layoutroute@onCreateView','plugin'),
	'langPath' => 'language',
	'viewPath' => 'views',
	'skinPath' => 'skin',
    'classes' => 'classes.*',
    'rewriteRule' =>'url',
	'theme' => array('pc' => array('default' => 'default','sysdefault' => 'green','sysseller' => 'green'),'mobile' => array('mobile' => 'default','sysdefault' => 'default','sysseller' => 'default')),
	'timezone'	=> 'Etc/GMT-8',
	'upload' => 'upload',
	'dbbackup' => 'backup/database',
	'safe' => 'cookie',
	'lang' => 'zh_sc',
	'debug'=> '1',
	'configExt'=> array('site_config'=>'config/site_config.php'),
	'encryptKey'=>'5bfb7f840bea9b4cfa05f7ac388c3760',
	'authorizeCode' => '',
);
?>