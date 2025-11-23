<?php
define('BASEPATH', true);
require 'application/helpers/cliente_helper.php';
$pf = preparar_dados_cliente([
    'nomeCliente' => 'Teste PF',
    'documento_pf' => '123.456.789-09',
    'telefone' => '(11) 99999-9999',
    'email' => 'pf@example.com',
    'cep' => '01001-000',
    'tipo_cliente' => 'fisica'
], 'fisica', 'secret');
print_r($pf);
$pj = preparar_dados_cliente([
    'nomeCliente' => 'Contato PJ',
    'documento_pj' => '12.345.678/0001-95',
    'razao_social' => 'Empresa X',
    'tipo_cliente' => 'juridica',
    'email' => 'pj@example.com'
], 'juridica', null);
print_r($pj);
?>
