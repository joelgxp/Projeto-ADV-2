<?php

if (! defined('BASEPATH')) {
    exit('No direct script access allowed');
}

// Rotas API V1
$route['api/v1'] = 'api/v1/ApiController/index';
$route['api/v1/audit'] = 'api/v1/ApiController/audit';
$route['api/v1/emitente'] = 'api/v1/ApiController/emitente';
$route['api/v1/calendario'] = 'api/v1/ApiController/calendario';
$route['api/v1/login'] = 'api/v1/UsuariosController/login';
$route['api/v1/reGenToken'] = 'api/v1/UsuariosController/reGenToken';
$route['api/v1/conta'] = 'api/v1/UsuariosController/conta';
$route['api/v1/clientes'] = 'api/v1/ClientesController/index';
$route['api/v1/clientes/(:num)'] = 'api/v1/ClientesController/index/$1';
$route['api/v1/servicos'] = 'api/v1/ServicosController/index';
$route['api/v1/servicos/(:num)'] = 'api/v1/ServicosController/index/$1';
$route['api/v1/usuarios'] = 'api/v1/UsuariosController/index';
$route['api/v1/usuarios/(:num)'] = 'api/v1/UsuariosController/index/$1';
$route['api/v1/processos'] = 'api/v1/ProcessosController/index';
$route['api/v1/processos/(:num)'] = 'api/v1/ProcessosController/index/$1';
$route['api/v1/prazos'] = 'api/v1/PrazosController/index';
$route['api/v1/prazos/(:num)'] = 'api/v1/PrazosController/index/$1';
$route['api/v1/audiencias'] = 'api/v1/AudienciasController/index';
$route['api/v1/audiencias/(:num)'] = 'api/v1/AudienciasController/index/$1';

/*
Routes for clients API
Rotas Para API area do cliente.
*/

$route['api/v1/client'] = 'api/v1/client/ClientProcessosController/index';
$route['api/v1/client/auth'] = 'api/v1/client/ClientLoginController/login';

$route['api/v1/client/processos'] = 'api/v1/client/ClientProcessosController/processos';
$route['api/v1/client/processos/(:num)'] = 'api/v1/client/ClientProcessosController/processos/$1';

$route['api/v1/client/prazos'] = 'api/v1/client/ClientPrazosController/index';
$route['api/v1/client/prazos/(:num)'] = 'api/v1/client/ClientPrazosController/index/$1';

$route['api/v1/client/audiencias'] = 'api/v1/client/ClientAudienciasController/index';
$route['api/v1/client/audiencias/(:num)'] = 'api/v1/client/ClientAudienciasController/index/$1';

$route['api/v1/client/cobrancas'] = 'api/v1/client/ClientCobrancasController/index';

