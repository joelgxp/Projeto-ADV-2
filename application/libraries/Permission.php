<?php

if (! defined('BASEPATH')) {
    exit('No direct script access allowed');
}

/**
 * Permission Class
 *
 * Biblioteca para controle de permissões
 *
 * @author      Ramon Silva
 * @copyright   Copyright (c) 2013, Ramon Silva.
 *
 * @since       Version 1.0
 * v... Visualizar
 * e... Editar
 * d... Deletar ou Desabilitar
 * c... Cadastrar
 */
class Permission
{
    private $permissions = [];

    private $table = 'permissoes'; //Nome tabela onde ficam armazenadas as permissões

    private $pk = 'idPermissao'; // Nome da chave primaria da tabela

    private $select = 'permissoes'; // Campo onde fica o array de permissoes.

    public function __construct()
    {
        log_message('debug', 'Permission Class Initialized');
        $this->CI = &get_instance();
        $this->CI->load->database();
    }

    public function checkPermission($idPermissao = null, $atividade = null)
    {
        if ($idPermissao == null || $atividade == null) {
            return false;
        }
        
        // Se as permissões não estiverem carregadas, requisita o carregamento
        if (empty($this->permissions)) {
            // Se não carregar retorna falso
            if (! $this->loadPermission($idPermissao)) {
                return false;
            }
        }

        // Verificar se o array de permissões não está vazio
        if (!empty($this->permissions) && isset($this->permissions[0]) && is_array($this->permissions[0])) {
            if (array_key_exists($atividade, $this->permissions[0])) {
                // compara a atividade requisitada com a permissão.
                if ($this->permissions[0][$atividade] == 1) {
                    return true;
                }
            }
        }

        return false;
    }

    private function loadPermission($id = null)
    {
        if ($id != null) {
            // Verificar se a tabela existe
            if (!$this->CI->db->table_exists($this->table)) {
                log_message('error', 'Tabela ' . $this->table . ' não existe');
                return false;
            }
            
            // Detectar estrutura da tabela
            $columns = $this->CI->db->list_fields($this->table);
            
            // Detectar coluna de ID
            $pk_column = in_array('idPermissao', $columns) ? 'idPermissao' : (in_array('id', $columns) ? 'id' : null);
            if (!$pk_column) {
                log_message('error', 'Coluna de ID não encontrada na tabela ' . $this->table);
                return false;
            }
            
            // Detectar coluna de permissões
            $perm_column = in_array('permissoes', $columns) ? 'permissoes' : (in_array('permissions', $columns) ? 'permissions' : null);
            if (!$perm_column) {
                log_message('error', 'Coluna de permissões não encontrada na tabela ' . $this->table);
                return false;
            }
            
            $this->CI->db->select($this->table . '.' . $perm_column);
            $this->CI->db->where($pk_column, $id);
            $this->CI->db->limit(1);
            
            $query = $this->CI->db->get($this->table);
            
            // Verificar se a query foi executada com sucesso
            if ($query === false) {
                $error = $this->CI->db->error();
                log_message('error', 'Erro na query loadPermission: ' . ($error['message'] ?? 'Erro desconhecido'));
                return false;
            }
            
            $array = $query->row_array();

            if ($array && count($array) > 0 && isset($array[$perm_column])) {
                $permissoes_serializadas = $array[$perm_column];
                
                // Verificar se está serializado
                if (is_string($permissoes_serializadas)) {
                    $array = @unserialize($permissoes_serializadas);
                    if ($array === false) {
                        log_message('error', 'Erro ao deserializar permissões');
                        return false;
                    }
                } else {
                    $array = $permissoes_serializadas;
                }
                
                // Verificar se é array
                if (is_array($array)) {
                    //Atribui as permissoes ao atributo permissions
                    $this->permissions = [$array];
                    return true;
                } else {
                    log_message('error', 'Permissões não são um array válido');
                    return false;
                }
            }
        }

        return false;
    }
}
