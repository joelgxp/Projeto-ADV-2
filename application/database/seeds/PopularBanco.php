<?php

class PopularBanco extends Seeder
{
    public function run()
    {
        echo "Iniciando popularização do banco de dados...\n\n";

        // Popular Clientes
        $this->popularClientes();
        
        // Popular Processos
        $this->popularProcessos();
        
        // Popular Prazos
        $this->popularPrazos();
        
        // Popular Audiências
        $this->popularAudiencias();

        echo "\nBanco de dados populado com sucesso!\n";
    }

    private function popularClientes()
    {
        echo "Populando clientes...\n";
        
        $clientes = [
            [
                'nomeCliente' => 'João Silva Santos',
                'pessoa_fisica' => 1,
                'tipo_cliente' => 'fisica',
                'documento' => '12345678901',
                'rg' => 'MG-12.345.678',
                'telefone' => '(31) 3333-4444',
                'celular' => '(31) 99999-8888',
                'email' => 'joao.silva@email.com',
                'senha' => password_hash('123456', PASSWORD_DEFAULT),
                'cep' => '30130-010',
                'rua' => 'Av. Afonso Pena',
                'numero' => '1000',
                'bairro' => 'Centro',
                'cidade' => 'Belo Horizonte',
                'estado' => 'MG',
                'dataCadastro' => date('Y-m-d', strtotime('-30 days')),
            ],
            [
                'nomeCliente' => 'Maria Oliveira Costa',
                'pessoa_fisica' => 1,
                'tipo_cliente' => 'fisica',
                'documento' => '98765432100',
                'rg' => 'SP-98.765.432',
                'telefone' => '(11) 2222-3333',
                'celular' => '(11) 98888-7777',
                'email' => 'maria.oliveira@email.com',
                'senha' => password_hash('123456', PASSWORD_DEFAULT),
                'cep' => '01310-100',
                'rua' => 'Av. Paulista',
                'numero' => '2000',
                'bairro' => 'Bela Vista',
                'cidade' => 'São Paulo',
                'estado' => 'SP',
                'dataCadastro' => date('Y-m-d', strtotime('-25 days')),
            ],
            [
                'nomeCliente' => 'Empresa ABC Ltda',
                'pessoa_fisica' => 0,
                'tipo_cliente' => 'juridica',
                'documento' => '12345678000190',
                'razao_social' => 'Empresa ABC Comércio e Serviços Ltda',
                'nome_fantasia' => 'ABC Ltda',
                'inscricao_estadual' => '123456789',
                'telefone' => '(31) 3444-5555',
                'celular' => '(31) 97777-6666',
                'email' => 'contato@abc.com.br',
                'senha' => password_hash('123456', PASSWORD_DEFAULT),
                'cep' => '30130-020',
                'rua' => 'Rua da Bahia',
                'numero' => '500',
                'bairro' => 'Centro',
                'cidade' => 'Belo Horizonte',
                'estado' => 'MG',
                'dataCadastro' => date('Y-m-d', strtotime('-20 days')),
            ],
            [
                'nomeCliente' => 'Pedro Almeida',
                'pessoa_fisica' => 1,
                'tipo_cliente' => 'fisica',
                'documento' => '11122233344',
                'rg' => 'RJ-11.222.333',
                'telefone' => '(21) 3333-2222',
                'celular' => '(21) 96666-5555',
                'email' => 'pedro.almeida@email.com',
                'senha' => password_hash('123456', PASSWORD_DEFAULT),
                'cep' => '20040-020',
                'rua' => 'Av. Rio Branco',
                'numero' => '300',
                'bairro' => 'Centro',
                'cidade' => 'Rio de Janeiro',
                'estado' => 'RJ',
                'dataCadastro' => date('Y-m-d', strtotime('-15 days')),
            ],
            [
                'nomeCliente' => 'Tech Solutions S.A.',
                'pessoa_fisica' => 0,
                'tipo_cliente' => 'juridica',
                'documento' => '98765432000111',
                'razao_social' => 'Tech Solutions Tecnologia S.A.',
                'nome_fantasia' => 'Tech Solutions',
                'inscricao_estadual' => '987654321',
                'telefone' => '(11) 4444-5555',
                'celular' => '(11) 95555-4444',
                'email' => 'contato@techsolutions.com.br',
                'senha' => password_hash('123456', PASSWORD_DEFAULT),
                'cep' => '01310-200',
                'rua' => 'Rua Augusta',
                'numero' => '1500',
                'bairro' => 'Consolação',
                'cidade' => 'São Paulo',
                'estado' => 'SP',
                'dataCadastro' => date('Y-m-d', strtotime('-10 days')),
            ],
        ];

        foreach ($clientes as $cliente) {
            $this->db->insert('clientes', $cliente);
            echo ".";
        }
        echo " OK\n";
    }

    private function popularProcessos()
    {
        echo "Populando processos...\n";
        
        // Buscar IDs de clientes e usuários
        $clientes = $this->db->get('clientes')->result();
        $usuarios = $this->db->get('usuarios')->result();
        
        if (empty($clientes) || empty($usuarios)) {
            echo " Aviso: Clientes ou usuários não encontrados. Pulando processos.\n";
            return;
        }

        $processos = [
            [
                'numeroProcesso' => '00001234520238130139',
                'classe' => 'Ação de Cobrança',
                'assunto' => 'Cobrança de Dívida',
                'tipo_processo' => 'civel',
                'vara' => '1ª Vara Cível',
                'comarca' => 'Belo Horizonte',
                'tribunal' => 'TJMG',
                'segmento' => 'estadual',
                'status' => 'em_andamento',
                'valorCausa' => 50000.00,
                'dataDistribuicao' => date('Y-m-d', strtotime('-60 days')),
                'clientes_id' => $clientes[0]->idClientes ?? $clientes[0]->id ?? null,
                'usuarios_id' => $usuarios[0]->idUsuarios ?? $usuarios[0]->id ?? null,
                'dataCadastro' => date('Y-m-d H:i:s', strtotime('-60 days')),
            ],
            [
                'numeroProcesso' => '00002345620238130140',
                'classe' => 'Ação Trabalhista',
                'assunto' => 'Rescisão Indireta',
                'tipo_processo' => 'trabalhista',
                'vara' => '2ª Vara do Trabalho',
                'comarca' => 'São Paulo',
                'tribunal' => 'TRT2',
                'segmento' => 'trabalho',
                'status' => 'em_andamento',
                'valorCausa' => 150000.00,
                'dataDistribuicao' => date('Y-m-d', strtotime('-45 days')),
                'clientes_id' => $clientes[1]->idClientes ?? $clientes[1]->id ?? null,
                'usuarios_id' => $usuarios[0]->idUsuarios ?? $usuarios[0]->id ?? null,
                'dataCadastro' => date('Y-m-d H:i:s', strtotime('-45 days')),
            ],
            [
                'numeroProcesso' => '00003456720238130141',
                'classe' => 'Ação de Indenização',
                'assunto' => 'Danos Morais',
                'tipo_processo' => 'civel',
                'vara' => '3ª Vara Cível',
                'comarca' => 'Rio de Janeiro',
                'tribunal' => 'TJRJ',
                'segmento' => 'estadual',
                'status' => 'suspenso',
                'valorCausa' => 30000.00,
                'dataDistribuicao' => date('Y-m-d', strtotime('-30 days')),
                'clientes_id' => $clientes[2]->idClientes ?? $clientes[2]->id ?? null,
                'usuarios_id' => $usuarios[0]->idUsuarios ?? $usuarios[0]->id ?? null,
                'dataCadastro' => date('Y-m-d H:i:s', strtotime('-30 days')),
            ],
            [
                'numeroProcesso' => '00004567820238130142',
                'classe' => 'Ação de Cobrança',
                'assunto' => 'Inadimplência',
                'tipo_processo' => 'civel',
                'vara' => '1ª Vara Cível',
                'comarca' => 'Belo Horizonte',
                'tribunal' => 'TJMG',
                'segmento' => 'estadual',
                'status' => 'em_andamento',
                'valorCausa' => 75000.00,
                'dataDistribuicao' => date('Y-m-d', strtotime('-20 days')),
                'clientes_id' => $clientes[3]->idClientes ?? $clientes[3]->id ?? null,
                'usuarios_id' => $usuarios[0]->idUsuarios ?? $usuarios[0]->id ?? null,
                'dataCadastro' => date('Y-m-d H:i:s', strtotime('-20 days')),
            ],
            [
                'numeroProcesso' => '00005678920238130143',
                'classe' => 'Ação Trabalhista',
                'assunto' => 'Horas Extras',
                'tipo_processo' => 'trabalhista',
                'vara' => '1ª Vara do Trabalho',
                'comarca' => 'São Paulo',
                'tribunal' => 'TRT2',
                'segmento' => 'trabalho',
                'status' => 'finalizado',
                'valorCausa' => 25000.00,
                'dataDistribuicao' => date('Y-m-d', strtotime('-90 days')),
                'clientes_id' => $clientes[4]->idClientes ?? $clientes[4]->id ?? null,
                'usuarios_id' => $usuarios[0]->idUsuarios ?? $usuarios[0]->id ?? null,
                'dataCadastro' => date('Y-m-d H:i:s', strtotime('-90 days')),
            ],
        ];

        foreach ($processos as $processo) {
            $this->db->insert('processos', $processo);
            echo ".";
        }
        echo " OK\n";
    }

    private function popularPrazos()
    {
        echo "Populando prazos...\n";
        
        $processos = $this->db->get('processos')->result();
        
        if (empty($processos)) {
            echo " Aviso: Processos não encontrados. Pulando prazos.\n";
            return;
        }

        $prazos = [
            [
                'processos_id' => $processos[0]->idProcessos ?? $processos[0]->id ?? null,
                'tipo' => 'Resposta',
                'descricao' => 'Prazo para apresentar resposta à inicial',
                'dataVencimento' => date('Y-m-d', strtotime('+15 days')),
                'status' => 'pendente',
                'observacoes' => 'Prazo contado a partir da citação',
            ],
            [
                'processos_id' => $processos[1]->idProcessos ?? $processos[1]->id ?? null,
                'tipo' => 'Recurso',
                'descricao' => 'Prazo para interpor recurso',
                'dataVencimento' => date('Y-m-d', strtotime('+5 days')),
                'status' => 'pendente',
                'observacoes' => 'Atenção: prazo fatal',
            ],
            [
                'processos_id' => $processos[2]->idProcessos ?? $processos[2]->id ?? null,
                'tipo' => 'Contestação',
                'descricao' => 'Prazo para contestar',
                'dataVencimento' => date('Y-m-d', strtotime('-2 days')),
                'status' => 'vencido',
                'observacoes' => 'Prazo vencido - verificar situação',
            ],
        ];

        foreach ($prazos as $prazo) {
            if ($prazo['processos_id']) {
                $this->db->insert('prazos', $prazo);
                echo ".";
            }
        }
        echo " OK\n";
    }

    private function popularAudiencias()
    {
        echo "Populando audiências...\n";
        
        $processos = $this->db->get('processos')->result();
        
        if (empty($processos)) {
            echo " Aviso: Processos não encontrados. Pulando audiências.\n";
            return;
        }

        $audiencias = [
            [
                'processos_id' => $processos[0]->idProcessos ?? $processos[0]->id ?? null,
                'tipo' => 'Audiência de Conciliação',
                'dataHora' => date('Y-m-d H:i:s', strtotime('+30 days')),
                'local' => 'Fórum Central - Sala 101',
                'observacoes' => 'Primeira audiência de conciliação',
                'status' => 'agendada',
            ],
            [
                'processos_id' => $processos[1]->idProcessos ?? $processos[1]->id ?? null,
                'tipo' => 'Audiência de Instrução',
                'dataHora' => date('Y-m-d H:i:s', strtotime('+45 days')),
                'local' => 'Fórum Regional - Sala 205',
                'observacoes' => 'Oitiva de testemunhas',
                'status' => 'agendada',
            ],
            [
                'processos_id' => $processos[2]->idProcessos ?? $processos[2]->id ?? null,
                'tipo' => 'Audiência de Conciliação',
                'dataHora' => date('Y-m-d H:i:s', strtotime('+20 days')),
                'local' => 'Fórum Central - Sala 102',
                'observacoes' => 'Tentativa de acordo',
                'status' => 'agendada',
            ],
        ];

        foreach ($audiencias as $audiencia) {
            if ($audiencia['processos_id']) {
                $this->db->insert('audiencias', $audiencia);
                echo ".";
            }
        }
        echo " OK\n";
    }
}

