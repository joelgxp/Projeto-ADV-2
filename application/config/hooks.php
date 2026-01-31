<?php

if (! defined('BASEPATH')) {
    exit('No direct script access allowed');
}
/*
| -------------------------------------------------------------------------
| Hooks
| -------------------------------------------------------------------------
| This file lets you define "hooks" to extend CI without hacking the core
| files.  Please see the user guide for info:
|
|	http://codeigniter.com/user_guide/general/hooks.html
|
*/

// compress output
$hook['display_override'][] = [
    'class' => '',
    'function' => 'compress',
    'filename' => 'compress.php',
    'filepath' => 'hooks',
];

$hook['pre_system'][] = [
    'class' => 'WhoopsHook',
    'function' => 'bootWhoops',
    'filename' => 'whoops.php',
    'filepath' => 'hooks',
    'params' => [],
];

// FASE 11: Rate Limiting (RN 12.3)
// DESABILITADO: Rate limiting implementado no MY_Controller::__construct()
// Hook causava erro "Attempt to read property 'load' on null" porque get_instance() retorna null no hook
// TODO: Reativar hook quando CodeIgniter estiver totalmente inicializado ou usar middleware alternativo
/*
$hook['pre_controller'][] = [
    'class' => 'Rate_limit',
    'function' => 'aplicar',
    'filename' => 'rate_limit.php',
    'filepath' => 'hooks',
    'params' => [],
];
*/

/* End of file hooks.php */
/* Location: ./application/config/hooks.php */
