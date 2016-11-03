<?php return array (
  'logs' => 
  array (
    'path' => 'backup/logs',
    'type' => 'file',
  ),
  'DB' => 
  array (
    'type' => 'mysqli',
    'tablePre' => 'iwebshop_',
    'read' => 
    array (
      0 => 
      array (
        'host' => '192.168.0.172:32771',
        'user' => 'root',
        'passwd' => '123456',
        'name' => 'jmj',
      ),
    ),
    'write' => 
    array (
      'host' => '192.168.0.172:32771',
      'user' => 'root',
      'passwd' => '123456',
      'name' => 'jmj',
    ),
  ),
  'interceptor' => 
  array (
    0 => 'themeroute@onCreateController',
    1 => 'layoutroute@onCreateView',
    2 => 'plugin',
  ),
  'langPath' => 'language',
  'viewPath' => 'views',
  'skinPath' => 'skin',
  'classes' => 'classes.*',
  'rewriteRule' => 'pathinfo',
  'theme' => 
  array (
    'pc' => 
    array (
      'default' => 'default',
      'sysdefault' => 'green',
      'sysseller' => 'green',
    ),
    'mobile' => 
    array (
      'mobile' => 'default',
      'sysdefault' => 'default',
      'sysseller' => 'default',
    ),
  ),
  'timezone' => 'Etc/GMT-8',
  'upload' => 'upload',
  'dbbackup' => 'backup/database',
  'sqlLog' => '1',
  'sqlDebug' => '0',
  'safe' => 'cookie',
  'lang' => 'zh_sc',
  'debug' => '1',
  'configExt' => 
  array (
    'site_config' => 'config/site_config.php',
  ),
  'encryptKey' => '693db335771319c04d195cca4f907fa9',
  'authorizeCode' => '201610093145',
)?>