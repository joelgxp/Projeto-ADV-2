<?php

if (! defined('BASEPATH')) {
    exit('No direct script access allowed');
}
/*
| -------------------------------------------------------------------------
| URI ROUTING
| -------------------------------------------------------------------------
| This file lets you re-map URI requests to specific controller functions.
|
| Typically there is a one-to-one relationship between a URL string
| and its corresponding controller class/method. The segments in a
| URL normally follow this pattern:
|
|	example.com/class/method/id/
|
| In some instances, however, you may want to remap this relationship
| so that a different class/function is called than the one
| corresponding to the URL.
|
| Please see the user guide for complete details:
|
|	http://codeigniter.com/user_guide/general/routing.html
|
| -------------------------------------------------------------------------
| RESERVED ROUTES
| -------------------------------------------------------------------------
|
| There area two reserved routes:
|
|	$route['default_controller'] = 'welcome';
|
| This route indicates which controller class should be loaded if the
| URI contains no data. In the above example, the "welcome" class
| would be loaded.
|
|	$route['404_override'] = 'errors/page_missing';
|
| This route will tell the Router what URI segments to use if those provided
| in the URL cannot be matched to a valid route.
|
*/

$route['default_controller'] = 'adv';
$route['404_override'] = '';

// Rotas dos novos módulos jurídicos
$route['processos'] = 'processos/gerenciar';
$route['processos/adicionar'] = 'processos/adicionar';
$route['processos/editar/(:num)'] = 'processos/editar/$1';
$route['processos/visualizar/(:num)'] = 'processos/visualizar/$1';
$route['processos/excluir'] = 'processos/excluir';

$route['prazos'] = 'prazos/gerenciar';
$route['prazos/adicionar'] = 'prazos/adicionar';
$route['prazos/editar/(:num)'] = 'prazos/editar/$1';
$route['prazos/visualizar/(:num)'] = 'prazos/visualizar/$1';
$route['prazos/excluir'] = 'prazos/excluir';

$route['audiencias'] = 'audiencias/gerenciar';
$route['audiencias/adicionar'] = 'audiencias/adicionar';
$route['audiencias/editar/(:num)'] = 'audiencias/editar/$1';
$route['audiencias/visualizar/(:num)'] = 'audiencias/visualizar/$1';
$route['audiencias/excluir'] = 'audiencias/excluir';

$route['planos'] = 'planos/gerenciar';
$route['planos/adicionar'] = 'planos/adicionar';
$route['planos/editar/(:num)'] = 'planos/editar/$1';
$route['planos/visualizar/(:num)'] = 'planos/visualizar/$1';
$route['planos/excluir'] = 'planos/excluir';

$route['consulta-processual'] = 'consultaProcessual/index';
$route['consulta-processual/consultar'] = 'consultaProcessual/consultar';
$route['consulta-processual/sincronizar/(:num)'] = 'consultaProcessual/sincronizar/$1';
$route['consulta-processual/buscar-cliente'] = 'consultaProcessual/buscar_cliente';
$route['consulta-processual/cadastrar-cliente-rapido'] = 'consultaProcessual/cadastrar_cliente_rapido';
$route['consulta-processual/salvar-processo'] = 'consultaProcessual/salvar_processo';

$route['pecas-geradas'] = 'pecasGeradas/listar';
$route['pecas-geradas/dashboard'] = 'pecasGeradas/dashboard';
$route['pecas-geradas/listar'] = 'pecasGeradas/listar';
$route['pecas-geradas/gerar'] = 'pecasGeradas/gerar';
$route['pecas-geradas/gerar/(:num)'] = 'pecasGeradas/gerar/$1';
$route['pecas-geradas/executar-geracao'] = 'pecasGeradas/executar_geracao';
$route['pecas-geradas/visualizar/(:num)'] = 'pecasGeradas/visualizar/$1';
$route['pecas-geradas/salvar-edicao/(:num)'] = 'pecasGeradas/salvar_edicao/$1';
$route['pecas-geradas/refinar'] = 'pecasGeradas/refinar';
$route['pecas-geradas/salvar-checklist/(:num)'] = 'pecasGeradas/salvar_checklist/$1';
$route['pecas-geradas/aprovar/(:num)'] = 'pecasGeradas/aprovar/$1';
$route['pecas-geradas/exportar/(:num)'] = 'pecasGeradas/exportar/$1';
$route['pecas-geradas/jurisprudencia'] = 'pecasGeradas/jurisprudencia';
$route['pecas-geradas/adicionar-jurisprudencia'] = 'pecasGeradas/adicionar_jurisprudencia';
$route['pecas-geradas/modelos'] = 'pecasGeradas/modelos';
$route['pecas-geradas/adicionar-modelo'] = 'pecasGeradas/adicionar_modelo';
$route['pecas-geradas/editar-modelo/(:num)'] = 'pecasGeradas/editar_modelo/$1';

/* End of file routes.php */
/* Location: ./application/config/routes.php */
