<?php

if (!defined('BASEPATH')) {
    exit('No direct script access allowed');
}

/**
 * Controller de Diagnóstico
 * 
 * Fornece ferramentas de diagnóstico para verificar a estrutura do banco de dados.
 * 
 * IMPORTANTE: Use apenas em ambiente de desenvolvimento!
 */
class Diagnostico extends MY_Controller
{
    public function __construct()
    {
        parent::__construct();
        
        // Apenas em desenvolvimento
        if (ENVIRONMENT === 'production') {
            show_404();
        }
    }

    /**
     * Verifica a estrutura do banco de dados
     */
    public function verificar_estrutura()
    {
        // Estrutura esperada (baseada no banco_limpo.sql)
        $estrutura_esperada = [
            'email_queue' => [
                'id' => 'INT(11)',
                'to' => 'VARCHAR(255)',
                'subject' => 'VARCHAR(255)',
                'message' => 'TEXT',
                'status' => 'VARCHAR(20)',
                'attempts' => 'INT(11)',
                'last_attempt' => 'DATETIME',
                'created_at' => 'DATETIME'
            ],
            'confirmacoes_email' => [
                'id' => 'INT(11)',
                'usuarios_id' => 'INT(11)',
                'token' => 'VARCHAR(255)',
                'data_expiracao' => 'DATETIME',
                'token_utilizado' => 'TINYINT(1)',
                'data_cadastro' => 'DATETIME'
            ],
            'tentativas_login' => [
                'id' => 'INT(11)',
                'email' => 'VARCHAR(200)',
                'ip_address' => 'VARCHAR(45)',
                'user_agent' => 'VARCHAR(500)',
                'sucesso' => 'TINYINT(1)',
                'data_hora' => 'DATETIME'
            ],
            'bloqueios_conta' => [
                'id' => 'INT(11)',
                'email' => 'VARCHAR(200)',
                'ip_address' => 'VARCHAR(45)',
                'tentativas_falhadas' => 'INT(11)',
                'bloqueado_ate' => 'DATETIME',
                'data_bloqueio' => 'DATETIME',
                'desbloqueado' => 'TINYINT(1)',
                'desbloqueado_por' => 'INT(11)',
                'data_desbloqueio' => 'DATETIME'
            ],
            'logs' => [
                'idLogs' => 'INT(11)',
                'usuario' => 'VARCHAR(100)',
                'ip' => 'VARCHAR(45)',
                'user_agent' => 'VARCHAR(500)',
                'tarefa' => 'VARCHAR(255)',
                'data' => 'DATE',
                'hora' => 'TIME'
            ],
            'usuarios' => [
                'idUsuarios' => 'INT(11)',
                'nome' => 'VARCHAR(100)',
                'oab' => 'VARCHAR(20)',
                'email' => 'VARCHAR(100)',
                'email_confirmado' => 'TINYINT(1)',
                'cpf' => 'VARCHAR(20)',
                'senha' => 'VARCHAR(200)',
                'situacao' => 'TINYINT(1)',
                'dataExpiracao' => 'DATE',
                'permissoes_id' => 'INT(11)',
                'dataCadastro' => 'DATETIME',
                'url_image_user' => 'VARCHAR(255)'
            ]
        ];

        // Função para normalizar tipo de coluna
        $normalizar_tipo = function($tipo) {
            $tipo = strtoupper(trim($tipo));
            $tipo = preg_replace('/\s+(NOT\s+NULL|NULL|DEFAULT|AUTO_INCREMENT|COMMENT).*/i', '', $tipo);
            return $tipo;
        };

        // Verificar estrutura
        $problemas = [];
        $sucesso = [];

        foreach ($estrutura_esperada as $tabela => $colunas_esperadas) {
            // Verificar se a tabela existe
            if (!$this->db->table_exists($tabela)) {
                $problemas[] = [
                    'tipo' => 'tabela_ausente',
                    'tabela' => $tabela,
                    'mensagem' => "Tabela '{$tabela}' não existe no banco de dados"
                ];
                continue;
            }
            
            // Obter colunas existentes
            $colunas_existentes = $this->db->list_fields($tabela);
            
            // Obter detalhes das colunas
            $query = $this->db->query("DESCRIBE `{$tabela}`");
            $colunas_detalhes = [];
            if ($query) {
                foreach ($query->result() as $coluna) {
                    $colunas_detalhes[$coluna->Field] = $coluna->Type;
                }
            }
            
            // Verificar colunas faltantes
            foreach ($colunas_esperadas as $coluna => $tipo_esperado) {
                if (!in_array($coluna, $colunas_existentes)) {
                    $problemas[] = [
                        'tipo' => 'coluna_ausente',
                        'tabela' => $tabela,
                        'coluna' => $coluna,
                        'tipo_esperado' => $tipo_esperado,
                        'mensagem' => "Coluna '{$coluna}' não existe na tabela '{$tabela}'"
                    ];
                } else {
                    // Verificar tipo (opcional)
                    if (isset($colunas_detalhes[$coluna])) {
                        $tipo_existente = $normalizar_tipo($colunas_detalhes[$coluna]);
                        $tipo_esperado_norm = $normalizar_tipo($tipo_esperado);
                        
                        if (stripos($tipo_existente, $tipo_esperado_norm) === false && 
                            stripos($tipo_esperado_norm, $tipo_existente) === false) {
                            $problemas[] = [
                                'tipo' => 'tipo_incompativel',
                                'tabela' => $tabela,
                                'coluna' => $coluna,
                                'tipo_esperado' => $tipo_esperado,
                                'tipo_existente' => $colunas_detalhes[$coluna],
                                'mensagem' => "Coluna '{$coluna}' na tabela '{$tabela}' tem tipo diferente do esperado"
                            ];
                        } else {
                            $sucesso[] = [
                                'tabela' => $tabela,
                                'coluna' => $coluna,
                                'tipo' => $colunas_detalhes[$coluna]
                            ];
                        }
                    }
                }
            }
        }

        // Preparar dados para a view
        $this->data['problemas'] = $problemas;
        $this->data['sucesso'] = $sucesso;
        $this->data['database'] = $this->db->database;
        $this->data['view'] = 'diagnostico/verificar_estrutura';
        
        return $this->layout();
    }
}

