<?php
defined('BASEPATH') OR exit('No direct script access allowed');
//RBAC权限验证
$hook['post_controller_constructor'] = array(
        'class'    => 'Common',
        'function' => 'auto_verify',
        'filename' => 'common_hook.php',
        'filepath' => 'hooks',
        'params'   => '',
);
$hook['display_override'] = array(
        'class'    => 'Common',
        'function' => 'view_override',
        'filename' => 'common_hook.php',
        'filepath' => 'hooks',
        'params'   => '',
);
//默认开启SESSION
$hook['pre_system'] = array(
        'class'    => '',
        'function' => 'session_start',
        'filename' => '',
        'filepath' => '',
        'params'   => '',
);