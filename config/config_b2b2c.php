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
                'host' => '112.74.30.84:32772',
                'user' => 'root',
                'passwd' => 'jmj123456',
                'name' => 'jmj_chenbo_b2b_dev',
                ),
            ),
        'write' =>
            array (
                'host' => '112.74.30.84:32772',
                'user' => 'root',
                'passwd' => 'jmj123456',
                'name' => 'jmj_chenbo_b2b_dev',
            ),
      ),
//  'DB' =>
//      array (
//          'type' => 'mysqli',
//          'tablePre' => 'iwebshop_',
//          'read' =>
//              array (
//                  0 =>
//                      array (
//                          'host' => '112.74.30.84:32772',
//                          'user' => 'root',
//                          'passwd' => 'jmj123456',
//                          'name' => 'jmj_b2b',
//                      ),
//              ),
//          'write' =>
//              array (
//                  'host' => '112.74.30.84:32772',
//                  'user' => 'root',
//                  'passwd' => 'jmj123456',
//                  'name' => 'jmj_b2b',
//              ),
//      ),
//  'DB' =>
//      array (
//          'type' => 'mysqli',
//          'tablePre' => 'iwebshop_',
//          'read' =>
//              array (
//                  0 =>
//                      array (
//                          'host' => '101.201.232.15:32768',
//                          'user' => 'root',
//                          'passwd' => '123456',
//                          'name' => 'jmj',
//                      ),
//              ),
//          'write' =>
//              array (
//                  'host' => '101.201.232.15:32768',
//                  'user' => 'root',
//                  'passwd' => '123456',
//                  'name' => 'jmj',
//              ),
//      ),
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
  'sqlLog' => '0',
  'sqlDebug' => '0',
  'safe' => 'cookie',
  'lang' => 'zh_sc',
  'debug' => '2',
  'configExt' => 
  array (
    'site_config' => 'config/site_config.php',
    'jmj_config' => 'config/jmj_config.php',
  ),
  'encryptKey' => '693db335771319c04d195cca4f907fa9',
  'authorizeCode' => '201610093145',
  'image_host' => 'http://m.jiumaojia.com',
  'image_host1' => 'http://192.168.0.42:8080',
//  'image_host1' => 'http://dev.jiumaojia.com',
)?>