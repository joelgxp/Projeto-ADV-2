<?php

if (! defined('BASEPATH')) {
    exit('No direct script access allowed');
}

class Tools extends CI_Controller
{
    /** @var \Faker\Generator */
    public $faker;

    /** @var Seeder */
    public $seeder;

    public function __construct()
    {
        parent::__construct();

        // Permitir acesso web apenas para métodos específicos
        $allowedMethodsViaWeb = ['seed'];
        $currentMethod = $this->router->method;
        
        if (! $this->input->is_cli_request() && !in_array($currentMethod, $allowedMethodsViaWeb)) {
            exit('Direct access is not allowed. This is a command line tool, use the terminal');
        }

        $this->load->dbforge();

        $this->load->library('Seeder');

        // initiate faker (opcional - só se estiver instalado)
        if (class_exists('Faker\Factory')) {
            $this->faker = Faker\Factory::create();
        } else {
            $this->faker = null;
        }

        // initiate seeder
        $this->seeder = Seeder::create();
    }

    public function index()
    {
        $this->help();
    }

    public function message($to = 'World')
    {
        echo "Hello {$to}!" . PHP_EOL;
    }

    public function help()
    {
        $result = "The following are the available command line interface commands\n\n";
        $result .= "php index.php tools migration \"file_name\"         Create new migration file\n";
        $result .= "php index.php tools migrate [\"version_number\"]    Run all migrations. The version number is optional.\n";
        $result .= "php index.php tools seeder \"file_name\"            Creates a new seed file.\n";
        $result .= "php index.php tools seed \"file_name\"              Run the specified seed file.\n";

        echo $result . PHP_EOL;
    }

    public function migration($name)
    {
        $this->make_migration_file($name);
    }

    public function migrate($version = null)
    {
        $this->load->library('migration');

        if ($version != null) {
            if ($this->migration->version($version) === false) {
                show_error($this->migration->error_string());
            } else {
                echo 'Migrations run successfully' . PHP_EOL;
            }

            return;
        }

        if ($this->migration->latest() === false) {
            show_error($this->migration->error_string());
        } else {
            echo 'Migrations run successfully' . PHP_EOL;
        }
    }

    public function seeder($name)
    {
        $this->make_seed_file($name);
    }

    public function seed($name = null)
    {
        // Permitir execução via web para seeds específicos
        $allowedSeedsViaWeb = ['PopularBanco'];
        $isWebRequest = !$this->input->is_cli_request();
        
        if ($isWebRequest && $name && !in_array($name, $allowedSeedsViaWeb)) {
            show_error('Este seed só pode ser executado via linha de comando.');
            return;
        }
        
        if ($name) {
            $this->seeder->call($name);

            if ($isWebRequest) {
                echo '<pre>Seed "' . $name . '" executado com sucesso!</pre>';
                echo '<p><a href="' . base_url() . '">Voltar ao sistema</a></p>';
            } else {
                echo 'Seeds run successfully' . PHP_EOL;
            }

            return;
        }

        // Seeds padrão só via CLI
        if ($isWebRequest) {
            show_error('Para executar seeds padrão, use: tools/seed/NomeDoSeed');
            return;
        }

        $seeds = [
            'Permissoes',
            'Usuarios',
            'Configuracoes',
        ];

        foreach ($seeds as $seed) {
            $this->seeder->call($seed);
        }

        echo 'Seeds run successfully' . PHP_EOL;
    }

    protected function make_migration_file($name)
    {
        $date = new DateTime();
        $timestamp = $date->format('YmdHis');

        $path = APPPATH . "database/migrations/$timestamp" . '_' . "$name.php";

        $my_migration = fopen($path, 'w') or exit('Unable to create migration file!');

        $migration_stub_path = APPPATH . 'database/stubs/migration.stub';

        $migration_stub = file_get_contents($migration_stub_path) or exit('Unable to open migration stub!');

        $migration_stub = preg_replace('/{name}/', $name, $migration_stub);

        fwrite($my_migration, $migration_stub);

        fclose($my_migration);

        echo "$path migration has successfully been created." . PHP_EOL;
    }

    protected function make_seed_file($name)
    {
        $className = ucfirst($name);

        $path = APPPATH . "database/seeds/$className.php";

        $my_seed = fopen($path, 'w') or exit('Unable to create seed file!');

        $seed_stub_path = APPPATH . 'database/stubs/seed.stub';

        $seed_stub = file_get_contents($seed_stub_path) or exit('Unable to open seed stub!');

        $seed_stub = preg_replace('/{name}/', $className, $seed_stub);

        fwrite($my_seed, $seed_stub);

        fclose($my_seed);

        echo "$path seeder has successfully been created." . PHP_EOL;
    }

    public function test_migration()
    {
        echo "=== Testando Migration ===\n\n";
        
        // Verificar se a coluna de teste existe
        $columns = $this->db->list_fields('usuarios');
        
        if (in_array('teste_migration', $columns)) {
            echo "✅ Coluna 'teste_migration' existe na tabela usuarios!\n\n";
        } else {
            echo "❌ Coluna 'teste_migration' NÃO foi encontrada!\n\n";
        }
        
        echo "Colunas da tabela usuarios:\n";
        foreach($columns as $col) {
            echo "- $col\n";
        }
    }

    public function verificar_estrutura()
    {
        echo "=== Estrutura da Tabela usuarios ===\n\n";
        
        if (!$this->db->table_exists('usuarios')) {
            echo "❌ Tabela 'usuarios' não existe!\n";
            return;
        }
        
        $columns = $this->db->list_fields('usuarios');
        
        echo "Colunas encontradas (" . count($columns) . "):\n";
        foreach($columns as $col) {
            echo "- $col\n";
        }
        
        echo "\n=== Verificando dados ===\n";
        $total = $this->db->count_all('usuarios');
        echo "Total de registros: $total\n";
        
        if ($total > 0) {
            $this->db->limit(1);
            $user = $this->db->get('usuarios')->row();
            if ($user) {
                echo "\nPrimeiro registro:\n";
                foreach((array)$user as $key => $value) {
                    echo "$key: $value\n";
                }
            }
        }
    }

    public function verificar_usuario()
    {
        echo "=== Verificando Usuários no Banco ===\n\n";
        
        // Verificar se existe usuário admin@admin.com
        $this->db->where('email', 'admin@admin.com');
        $user = $this->db->get('usuarios')->row();
        
        if ($user) {
            echo "✅ Usuário encontrado!\n\n";
            echo "Email: " . $user->email . "\n";
            echo "Nome: " . $user->nome . "\n";
            echo "Situação: " . ($user->situacao == 1 ? 'Ativo' : 'Inativo') . "\n";
            echo "Permissões ID: " . $user->permissoes_id . "\n";
        } else {
            echo "❌ Usuário admin@admin.com NÃO encontrado!\n\n";
            
            $total = $this->db->count_all('usuarios');
            echo "Total de usuários no banco: $total\n\n";
            
            if ($total == 0) {
                echo "⚠️  Nenhum usuário encontrado no banco!\n";
                echo "Deseja criar o usuário admin? Execute: php index.php tools criar_usuario\n";
            }
        }
    }

    public function criar_usuario()
    {
        echo "=== Criando Usuário Admin ===\n\n";
        
        try {
            // Verificar conexão com banco
            if (!$this->db->conn_id) {
                echo "❌ Erro: Não há conexão com o banco de dados!\n";
                echo "Verifique as configurações em application/.env\n";
                return;
            }
            
            // Verificar se tabela usuarios existe
            if (!$this->db->table_exists('usuarios')) {
                echo "❌ Erro: Tabela 'usuarios' não existe!\n";
                echo "Execute as migrations primeiro: php index.php tools migrate\n";
                return;
            }
            
            // Verificar estrutura da tabela primeiro
            $columns = $this->db->list_fields('usuarios');
            echo "Colunas disponíveis na tabela: " . implode(', ', $columns) . "\n\n";
            
            // Detectar estrutura da tabela (Adv padrão vs estrutura customizada)
            $is_adv_structure = in_array('idUsuarios', $columns) && in_array('email', $columns);
            $is_custom_structure = in_array('id', $columns) && in_array('usuario', $columns);
            
            // Verificar qual coluna usar para email/usuario
            $email_column = null;
            if (in_array('email', $columns)) {
                $email_column = 'email';
            } elseif (in_array('usuario', $columns)) {
                $email_column = 'usuario';
            } elseif (in_array('Email', $columns)) {
                $email_column = 'Email';
            } elseif (in_array('EMAIL', $columns)) {
                $email_column = 'EMAIL';
            } else {
                echo "❌ Erro: Coluna de email/usuario não encontrada na tabela!\n";
                echo "Colunas disponíveis: " . implode(', ', $columns) . "\n";
                return;
            }
            
            // Verificar se já existe
            $this->db->where($email_column, 'admin@admin.com');
            $query = $this->db->get('usuarios');
            
            if ($query === false) {
                $error = $this->db->error();
                echo "❌ Erro ao consultar banco: " . ($error['message'] ?? 'Erro desconhecido') . "\n";
                return;
            }
            
            $existe = $query->row();
            
            if ($existe) {
                echo "⚠️  Usuário admin@admin.com já existe!\n";
                $user_email = isset($existe->email) ? $existe->email : (isset($existe->usuario) ? $existe->usuario : (isset($existe->Email) ? $existe->Email : 'N/A'));
                $user_nome = isset($existe->nome) ? $existe->nome : 'N/A';
                $user_id = isset($existe->idUsuarios) ? $existe->idUsuarios : (isset($existe->id) ? $existe->id : 'N/A');
                echo "Email/Usuário: " . $user_email . "\n";
                echo "Nome: " . $user_nome . "\n";
                echo "ID: " . $user_id . "\n";
                return;
            }
            
            // Criar usuário baseado na estrutura detectada
            $data = [];
            
            if ($is_custom_structure) {
                // Estrutura customizada (como no servidor)
                echo "📋 Detectada estrutura customizada\n\n";
                
                $data['nome'] = 'Admin';
                
                // Verificar CPF único
                if (in_array('cpf', $columns)) {
                    $cpf_tentativas = [
                        '111.111.111-11',
                        '222.222.222-22',
                        '333.333.333-33',
                        '444.444.444-44',
                        '555.555.555-55',
                        '666.666.666-66',
                        '777.777.777-77',
                        '888.888.888-88',
                        '999.999.999-99',
                        '123.456.789-00'
                    ];
                    
                    $cpf_valido = null;
                    foreach ($cpf_tentativas as $cpf) {
                        $this->db->where('cpf', $cpf);
                        $existe_cpf = $this->db->get('usuarios')->row();
                        if (!$existe_cpf) {
                            $cpf_valido = $cpf;
                            break;
                        }
                    }
                    
                    if (!$cpf_valido) {
                        // Gerar CPF único até encontrar um disponível
                        $tentativas = 0;
                        do {
                            $cpf_gerado = '999.' . str_pad(rand(100, 999), 3, '0', STR_PAD_LEFT) . '.' . str_pad(rand(100, 999), 3, '0', STR_PAD_LEFT) . '-' . str_pad(rand(10, 99), 2, '0', STR_PAD_LEFT);
                            $this->db->where('cpf', $cpf_gerado);
                            $existe_cpf = $this->db->get('usuarios')->row();
                            $tentativas++;
                        } while ($existe_cpf && $tentativas < 10);
                        
                        if (!$existe_cpf) {
                            $cpf_valido = $cpf_gerado;
                            echo "⚠️  CPFs comuns já existem, usando CPF gerado: $cpf_gerado\n";
                        } else {
                            echo "⚠️  Aviso: Não foi possível gerar CPF único após 10 tentativas. Tentando criar sem CPF...\n";
                        }
                    }
                    
                    if ($cpf_valido) {
                        $data['cpf'] = $cpf_valido;
                    }
                }
                
                if (in_array('usuario', $columns)) {
                    $data['usuario'] = 'admin@admin.com';
                }
                if (in_array('senha', $columns)) {
                    $data['senha'] = password_hash('123456', PASSWORD_DEFAULT);
                }
                if (in_array('senha_original', $columns)) {
                    $data['senha_original'] = '123456';
                }
                if (in_array('nivel', $columns)) {
                    $data['nivel'] = 'admin';
                }
                if (in_array('data_cadastro', $columns)) {
                    $data['data_cadastro'] = date('Y-m-d H:i:s');
                } elseif (in_array('dataCadastro', $columns)) {
                    $data['dataCadastro'] = date('Y-m-d');
                }
                if (in_array('ativo', $columns)) {
                    $data['ativo'] = 1;
                }
                if (in_array('situacao', $columns)) {
                    $data['situacao'] = 1;
                }
                if (in_array('cep', $columns)) {
                    $data['cep'] = '01024-900';
                }
            } else {
                // Estrutura padrão Adv
                echo "📋 Detectada estrutura padrão Adv\n\n";
                
                $colunas_map = [
                    'nome' => 'Admin',
                    'rg' => 'MG-25.502.560',
                    'cpf' => '517.565.356-39',
                    'cep' => '01024-900',
                    'rua' => 'R. Cantareira',
                    'numero' => '306',
                    'bairro' => 'Centro Histórico de São Paulo',
                    'cidade' => 'São Paulo',
                    'estado' => 'SP',
                    'email' => 'admin@admin.com',
                    'senha' => password_hash('123456', PASSWORD_DEFAULT),
                    'telefone' => '0000-0000',
                    'celular' => '',
                    'situacao' => 1,
                    'dataCadastro' => date('Y-m-d'),
                    'permissoes_id' => 1,
                    'dataExpiracao' => '2030-01-01',
                ];
                
                // Adicionar apenas colunas que existem na tabela
                foreach ($colunas_map as $coluna => $valor) {
                    if (in_array($coluna, $columns)) {
                        $data[$coluna] = $valor;
                    }
                }
                
                // Usar a coluna de email correta
                if ($email_column && $email_column !== 'email') {
                    unset($data['email']);
                    $data[$email_column] = 'admin@admin.com';
                }
            }
            
            if (empty($data)) {
                echo "❌ Erro: Nenhuma coluna válida encontrada para criar o usuário!\n";
                return;
            }
            
            echo "Dados que serão inseridos:\n";
            foreach ($data as $key => $value) {
                if ($key !== 'senha') {
                    echo "  $key: $value\n";
                } else {
                    echo "  $key: [hash oculto]\n";
                }
            }
            echo "\n";
            
            if ($this->db->insert('usuarios', $data)) {
                $id = $this->db->insert_id();
                echo "✅ Usuário criado com sucesso!\n\n";
                echo "ID: $id\n";
                echo "Email/Usuário: admin@admin.com\n";
                echo "Senha: 123456\n";
                echo "⚠️  IMPORTANTE: Altere a senha após o primeiro login!\n";
            } else {
                $error = $this->db->error();
                echo "❌ Erro ao criar usuário!\n";
                echo "Código: " . ($error['code'] ?? 'N/A') . "\n";
                echo "Mensagem: " . ($error['message'] ?? 'Erro desconhecido') . "\n";
            }
        } catch (Exception $e) {
            echo "❌ Exceção: " . $e->getMessage() . "\n";
            echo "Arquivo: " . $e->getFile() . "\n";
            echo "Linha: " . $e->getLine() . "\n";
        }
    }

    public function listar_tabelas()
    {
        try {
            $tables = $this->db->list_tables();
            
            echo "=== TABELAS DO BANCO DE DADOS ===\n\n";
            echo "Total de tabelas: " . count($tables) . "\n\n";
            
            // Separar tabelas do sistema jurídico e outras
            $tabelas_juridicas = ['processos', 'movimentacoes_processuais', 'prazos', 'audiencias', 'documentos_processuais', 'servicos_juridicos'];
            $tabelas_principais = ['clientes', 'usuarios', 'lancamentos', 'cobrancas', 'permissoes', 'configuracoes', 'contas', 'categorias'];
            $tabelas_sistema = ['ci_sessions', 'migrations'];
            
            $juridicas = [];
            $principais = [];
            $sistema = [];
            $outras = [];
            
            foreach ($tables as $table) {
                if (in_array($table, $tabelas_juridicas)) {
                    $juridicas[] = $table;
                } elseif (in_array($table, $tabelas_principais)) {
                    $principais[] = $table;
                } elseif (in_array($table, $tabelas_sistema)) {
                    $sistema[] = $table;
                } else {
                    $outras[] = $table;
                }
            }
            
            if (!empty($juridicas)) {
                echo "📋 TABELAS DO SISTEMA JURÍDICO:\n";
                foreach ($juridicas as $table) {
                    $count = $this->db->count_all($table);
                    echo "  ✅ $table ($count registros)\n";
                }
                echo "\n";
            }
            
            if (!empty($principais)) {
                echo "📊 TABELAS PRINCIPAIS:\n";
                foreach ($principais as $table) {
                    $count = $this->db->count_all($table);
                    echo "  ✅ $table ($count registros)\n";
                }
                echo "\n";
            }
            
            if (!empty($sistema)) {
                echo "⚙️  TABELAS DO SISTEMA:\n";
                foreach ($sistema as $table) {
                    $count = $this->db->count_all($table);
                    echo "  ✅ $table ($count registros)\n";
                }
                echo "\n";
            }
            
            if (!empty($outras)) {
                echo "📁 OUTRAS TABELAS:\n";
                foreach ($outras as $table) {
                    $count = $this->db->count_all($table);
                    echo "  ✅ $table ($count registros)\n";
                }
                echo "\n";
            }
            
            // Verificar estrutura de algumas tabelas importantes
            echo "=== VERIFICAÇÃO DE ESTRUTURA ===\n\n";
            
            // Verificar tabela processos
            if ($this->db->table_exists('processos')) {
                echo "✅ Tabela 'processos' existe\n";
                $columns = $this->db->list_fields('processos');
                echo "   Colunas: " . implode(', ', $columns) . "\n";
            } else {
                echo "❌ Tabela 'processos' NÃO existe\n";
            }
            echo "\n";
            
            // Verificar tabela prazos
            if ($this->db->table_exists('prazos')) {
                echo "✅ Tabela 'prazos' existe\n";
                $columns = $this->db->list_fields('prazos');
                echo "   Colunas: " . implode(', ', $columns) . "\n";
            } else {
                echo "❌ Tabela 'prazos' NÃO existe\n";
            }
            echo "\n";
            
            // Verificar tabela audiencias
            if ($this->db->table_exists('audiencias')) {
                echo "✅ Tabela 'audiencias' existe\n";
                $columns = $this->db->list_fields('audiencias');
                echo "   Colunas: " . implode(', ', $columns) . "\n";
            } else {
                echo "❌ Tabela 'audiencias' NÃO existe\n";
            }
            echo "\n";
            
            // Verificar tabela servicos_juridicos
            if ($this->db->table_exists('servicos_juridicos')) {
                echo "✅ Tabela 'servicos_juridicos' existe\n";
                $columns = $this->db->list_fields('servicos_juridicos');
                echo "   Colunas: " . implode(', ', $columns) . "\n";
            } else {
                echo "⚠️  Tabela 'servicos_juridicos' NÃO existe (pode estar como 'servicos')\n";
            }
            echo "\n";
            
        } catch (Exception $e) {
            echo "❌ Erro ao listar tabelas: " . $e->getMessage() . "\n";
        }
    }

    public function verificar_clientes()
    {
        try {
            if (!$this->db->table_exists('clientes')) {
                echo "❌ Tabela 'clientes' não existe.\n";
                return;
            }

            echo "=== ESTRUTURA DA TABELA CLIENTES ===\n\n";
            
            $columns = $this->db->list_fields('clientes');
            echo "Total de colunas: " . count($columns) . "\n\n";
            
            // Separar campos por categoria
            $campos_basicos = ['idClientes', 'nomeCliente', 'documento', 'email', 'telefone', 'celular', 'dataCadastro'];
            $campos_endereco = ['rua', 'numero', 'bairro', 'cidade', 'estado', 'cep', 'complemento', 'contato'];
            $campos_pf = ['rg', 'filiacao', 'profissao', 'sexo', 'pessoa_fisica'];
            $campos_pj = ['razao_social', 'inscricao_estadual', 'inscricao_municipal', 'representantes_legais', 'socios', 'ramo_atividade'];
            $campos_juridicos = ['oab', 'tipo_cliente', 'observacoes_juridicas'];
            $campos_adicionais = ['emails_adicionais', 'telefones_adicionais', 'senha', 'fornecedor'];
            
            $basicos = [];
            $endereco = [];
            $pf = [];
            $pj = [];
            $juridicos = [];
            $adicionais = [];
            $outros = [];
            
            foreach ($columns as $col) {
                if (in_array($col, $campos_basicos)) {
                    $basicos[] = $col;
                } elseif (in_array($col, $campos_endereco)) {
                    $endereco[] = $col;
                } elseif (in_array($col, $campos_pf)) {
                    $pf[] = $col;
                } elseif (in_array($col, $campos_pj)) {
                    $pj[] = $col;
                } elseif (in_array($col, $campos_juridicos)) {
                    $juridicos[] = $col;
                } elseif (in_array($col, $campos_adicionais)) {
                    $adicionais[] = $col;
                } else {
                    $outros[] = $col;
                }
            }
            
            if (!empty($basicos)) {
                echo "📋 CAMPOS BÁSICOS:\n";
                foreach ($basicos as $col) {
                    echo "  ✅ $col\n";
                }
                echo "\n";
            }
            
            if (!empty($endereco)) {
                echo "📍 CAMPOS DE ENDEREÇO:\n";
                foreach ($endereco as $col) {
                    echo "  ✅ $col\n";
                }
                echo "\n";
            }
            
            if (!empty($pf)) {
                echo "👤 CAMPOS PESSOA FÍSICA (PF):\n";
                foreach ($pf as $col) {
                    echo "  ✅ $col\n";
                }
                echo "\n";
            }
            
            if (!empty($pj)) {
                echo "🏢 CAMPOS PESSOA JURÍDICA (PJ):\n";
                foreach ($pj as $col) {
                    echo "  ✅ $col\n";
                }
                echo "\n";
            }
            
            if (!empty($juridicos)) {
                echo "⚖️  CAMPOS JURÍDICOS:\n";
                foreach ($juridicos as $col) {
                    echo "  ✅ $col\n";
                }
                echo "\n";
            }
            
            if (!empty($adicionais)) {
                echo "➕ CAMPOS ADICIONAIS:\n";
                foreach ($adicionais as $col) {
                    echo "  ✅ $col\n";
                }
                echo "\n";
            }
            
            if (!empty($outros)) {
                echo "📁 OUTROS CAMPOS:\n";
                foreach ($outros as $col) {
                    echo "  ✅ $col\n";
                }
                echo "\n";
            }
            
            // Verificar campos que deveriam existir mas não existem
            echo "=== VERIFICAÇÃO DE CAMPOS ESPERADOS ===\n\n";
            
            $campos_esperados_pf = ['rg', 'filiacao', 'profissao'];
            $campos_esperados_pj = ['razao_social', 'inscricao_estadual', 'inscricao_municipal', 'representantes_legais', 'socios'];
            
            echo "Campos PF esperados:\n";
            foreach ($campos_esperados_pf as $campo) {
                if (in_array($campo, $columns)) {
                    echo "  ✅ $campo\n";
                } else {
                    echo "  ❌ $campo (FALTANDO)\n";
                }
            }
            echo "\n";
            
            echo "Campos PJ esperados:\n";
            foreach ($campos_esperados_pj as $campo) {
                if (in_array($campo, $columns)) {
                    echo "  ✅ $campo\n";
                } else {
                    echo "  ❌ $campo (FALTANDO)\n";
                }
            }
            echo "\n";
            
        } catch (Exception $e) {
            echo "❌ Erro ao verificar clientes: " . $e->getMessage() . "\n";
        }
    }
}
