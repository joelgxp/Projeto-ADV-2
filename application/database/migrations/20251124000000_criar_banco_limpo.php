<?php

defined('BASEPATH') or exit('No direct script access allowed');

/**
 * Migration para criar banco de dados limpo
 * 
 * ATENÇÃO: Esta migration cria todas as tabelas do zero.
 * Use apenas se quiser recriar o banco completamente.
 * 
 * Para usar: Execute esta migration após remover todas as tabelas.
 */
class Migration_criar_banco_limpo extends CI_Migration
{
    public function up()
    {
        echo "🚀 Criando banco de dados limpo...\n\n";

        // Esta migration apenas garante que todas as tabelas existam
        // As migrations individuais já criam cada tabela
        
        echo "✅ Esta migration garante que todas as migrations anteriores foram executadas.\n";
        echo "⚠️  Para criar o banco do zero, execute o script SQL: banco_limpo.sql\n";
        echo "⚠️  Ou remova todas as tabelas manualmente e execute todas as migrations.\n\n";
        
        // Verificar se tabelas essenciais existem
        $tabelas_essenciais = [
            'usuarios',
            'permissoes',
            'clientes',
            'processos',
            'prazos',
            'audiencias',
        ];
        
        $faltando = [];
        foreach ($tabelas_essenciais as $tabela) {
            if (!$this->db->table_exists($tabela)) {
                $faltando[] = $tabela;
            }
        }
        
        if (!empty($faltando)) {
            echo "⚠️  Tabelas faltando: " . implode(', ', $faltando) . "\n";
            echo "⚠️  Execute as migrations individuais primeiro!\n";
            return false;
        }
        
        echo "✅ Todas as tabelas essenciais existem!\n";
        return true;
    }

    public function down()
    {
        echo "⚠️  Rollback não recomendado para esta migration.\n";
        echo "⚠️  Use backup para restaurar o banco se necessário.\n";
    }
}

