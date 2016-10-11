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
			array('host'=>'192.168.10.180:32772','user'=>'root','passwd'=>'123456','name'=>'jmj_dev4.5'),
		),

		'write'=>array(
			'host'=>'192.168.10.180:32772','user'=>'root','passwd'=>'123456','name'=>'jmj_dev4.5',
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
	'debug'=> '0',
	'configExt'=> array('site_config'=>'config/site_config.php'),
	'encryptKey'=>'3ca30c534c29eccff83a3e43d0ba9562',
	'authorizeCode' => '201610093145',
);
?>