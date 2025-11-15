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

        // can only be called from the command line
        if (! $this->input->is_cli_request()) {
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
        if ($name) {
            $this->seeder->call($name);

            echo 'Seeds run successfully' . PHP_EOL;

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
            
            // Verificar se já existe
            $this->db->where('email', 'admin@admin.com');
            $query = $this->db->get('usuarios');
            
            if ($query === false) {
                $error = $this->db->error();
                echo "❌ Erro ao consultar banco: " . ($error['message'] ?? 'Erro desconhecido') . "\n";
                return;
            }
            
            $existe = $query->row();
            
            if ($existe) {
                echo "⚠️  Usuário admin@admin.com já existe!\n";
                echo "Email: " . $existe->email . "\n";
                echo "Nome: " . $existe->nome . "\n";
                echo "ID: " . $existe->idUsuarios . "\n";
                return;
            }
            
            // Verificar se existe permissão ID 1
            if ($this->db->table_exists('permissoes')) {
                $this->db->where('idPermissao', 1);
                $permissao = $this->db->get('permissoes')->row();
                
                if (!$permissao) {
                    echo "⚠️  Aviso: Permissão ID 1 não existe!\n";
                    echo "Criando usuário mesmo assim...\n";
                    echo "Você pode executar depois: php index.php tools seed Permissoes\n\n";
                }
            } else {
                echo "⚠️  Aviso: Tabela 'permissoes' não existe!\n";
                echo "Criando usuário mesmo assim...\n\n";
            }
            
            // Criar usuário
            $data = [
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
            
            if ($this->db->insert('usuarios', $data)) {
                $id = $this->db->insert_id();
                echo "✅ Usuário criado com sucesso!\n\n";
                echo "ID: $id\n";
                echo "Email: admin@admin.com\n";
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
}
