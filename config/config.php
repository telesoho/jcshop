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
        'host' => 'localhost:3306',
        'user' => 'root',
        'passwd' => '',
        'name' => 'jmj_dev',
      ),
    ),
    'write' => 
    array (
      'host' => 'localhost:3306',
      'user' => 'root',
      'passwd' => '',
      'name' => 'jmj_dev',
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
  'safe' => 'session',
  'lang' => 'zh_sc',
  'debug' => '2',
  'configExt' => 
  array (
    'site_config' => 'config/site_config.php',
  ),
  'encryptKey' => '693db335771319c04d195cca4f907fa9',
  'authorizeCode' => '201610093145',
  'image_host' => 'http://122.208.129.43',
  'cdn_host' => '',
  'qrcode_url_template' => 'http://haibaobei-ec.com/site/index.php?controller=site&amp;action=products&amp;id={{product_id}}&amp;seller_id={{seller_id}}',
)?>