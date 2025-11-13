# Referência de Arquitetura e Tecnologias - MapOS

## 📋 Índice
1. [Visão Geral](#visão-geral)
2. [Arquitetura do Sistema](#arquitetura-do-sistema)
3. [Stack Tecnológico](#stack-tecnológico)
4. [Estrutura de Diretórios](#estrutura-de-diretórios)
5. [Padrões Arquiteturais](#padrões-arquiteturais)
6. [Fluxos Principais](#fluxos-principais)
7. [Integrações](#integrações)
8. [Configurações e Ambiente](#configurações-e-ambiente)
9. [Segurança](#segurança)
10. [Banco de Dados](#banco-de-dados)

---

## Visão Geral

### Sobre o Sistema
- **Nome**: MapOS (Map-OS)
- **Tipo**: Sistema de Controle de Ordens de Serviço (SaaS)
- **Versão Atual**: 4.52.0
- **Licença**: Apache 2.0
- **Arquitetura**: Monolítica MVC (Model-View-Controller)

### Características Principais
- Sistema web completo para gestão de ordens de serviço
- Portal do cliente integrado
- API REST para integrações
- Múltiplos gateways de pagamento
- Sistema de permissões granular
- Auditoria de ações
- Relatórios em PDF e XLSX

---

## Arquitetura do Sistema

### Padrão Arquitetural
**MVC (Model-View-Controller)** baseado no framework CodeIgniter 3

### Camadas da Aplicação

#### 1. Camada de Apresentação (View)
- Views PHP com HTML/CSS/JavaScript
- Templates reutilizáveis (tema/topo, tema/menu, tema/conteudo, tema/rodape)
- Sistema de views dinâmicas carregadas via variável `$view`
- Separação entre área administrativa e área do cliente

#### 2. Camada de Controle (Controller)
- Controllers herdam de `MY_Controller` (extensão de `CI_Controller`)
- Controllers específicos por módulo (Os, Clientes, Vendas, etc.)
- Controllers de API separados em namespace `api/v1`
- Validação de permissões em cada método
- Carregamento automático de configurações do sistema

#### 3. Camada de Dados (Model)
- Models específicos por entidade
- Uso do Query Builder do CodeIgniter
- Métodos CRUD padronizados
- Relacionamentos via JOINs

### Fluxo de Requisição

```
Cliente → index.php → Router → Controller → Model → Database
                                    ↓
                                 View ← Data
```

### Componentes Principais

#### MY_Controller (Controller Base)
- Carrega configurações do banco automaticamente
- Verifica autenticação de sessão
- Disponibiliza `$this->data` para todas as views
- Método `layout()` para renderização padrão

#### Sistema de Configurações
- Configurações armazenadas na tabela `configuracoes`
- Carregadas dinamicamente no construtor do controller base
- Acessíveis via `$this->data['configuration']`
- Incluem: tema, paginação, notificações, controles de sistema

#### Sistema de Permissões
- Permissões serializadas na tabela `permissoes`
- Biblioteca `Permission` para verificação
- Padrão de nomenclatura: `v` (visualizar), `e` (editar), `d` (deletar), `c` (cadastrar)
- Verificação em cada método do controller

---

## Stack Tecnológico

### Backend

#### Framework Core
- **CodeIgniter 3.1.13** - Framework PHP MVC
- **PHP 8.3+** - Linguagem de programação
- **Composer 2+** - Gerenciador de dependências

#### Banco de Dados
- **MySQL 5.7+** ou **MySQL 8.0+**
- **Query Builder** do CodeIgniter (proteção SQL Injection)
- **Migrations** para versionamento de schema

#### Bibliotecas PHP Principais
- **mpdf/mpdf 8.2.5** - Geração de PDFs
- **mercadopago/dx-php 3.7** - Integração Mercado Pago
- **efipay/sdk-php-apis-efi 1.13.0** - Integração EFI (Gerencianet)
- **codephix/asaas-sdk 2.0.12** - Integração Asaas
- **piggly/php-pix 2.0.2** - Geração de QR Code PIX
- **vlucas/phpdotenv 5.6.2** - Variáveis de ambiente
- **mk-j/php_xlsxwriter 0.38.0** - Geração de arquivos Excel
- **phpoffice/phpword 0.18.3** - Manipulação de documentos Word
- **mpdf/qrcode 1.2.1** - Geração de QR Codes
- **filp/whoops 2.18.3** - Páginas de erro formatadas

### Frontend

#### Frameworks e Bibliotecas
- **Bootstrap** (Tema Matrix Admin) - Framework CSS
- **jQuery** - Biblioteca JavaScript
- **jQuery UI** - Componentes de interface
- **DataTables** - Tabelas interativas com paginação
- **Trumbowyg** - Editor WYSIWYG
- **Highcharts** - Gráficos e visualizações
- **SweetAlert** - Alertas modernos
- **Font Awesome** - Ícones

### Infraestrutura

#### Servidor Web
- **Nginx** (Docker) ou **Apache** (instalação tradicional)
- Suporte a mod_rewrite para URLs amigáveis

#### Containerização
- **Docker** e **Docker Compose**
- Containers: Nginx, PHP-FPM, MySQL, phpMyAdmin

#### Processamento
- **PHP-FPM** para processamento PHP
- **Cron Jobs** para tarefas agendadas (envio de emails)

---

## Estrutura de Diretórios

### Estrutura Principal

```
/
├── application/              # Código da aplicação
│   ├── cache/               # Cache do sistema
│   ├── config/               # Arquivos de configuração
│   │   ├── config.php       # Configurações gerais
│   │   ├── database.php     # Configuração do banco
│   │   ├── routes.php       # Rotas principais
│   │   ├── routes_api.php   # Rotas da API
│   │   └── ...
│   ├── controllers/         # Controllers da aplicação
│   │   ├── api/             # Controllers da API REST
│   │   │   └── v1/          # Versão 1 da API
│   │   └── ...              # Controllers principais
│   ├── core/                # Classes core customizadas
│   │   └── MY_Controller.php # Controller base
│   ├── database/            # Migrations e seeds
│   │   ├── migrations/      # Arquivos de migration
│   │   └── seeds/          # Seeders de dados
│   ├── helpers/             # Helpers customizados
│   ├── hooks/               # Hooks do CodeIgniter
│   ├── language/            # Arquivos de idioma
│   │   └── pt-br/          # Traduções em português
│   ├── libraries/           # Bibliotecas customizadas
│   │   ├── REST_Controller.php
│   │   ├── Authorization_Token.php
│   │   └── Permission.php
│   ├── models/             # Models da aplicação
│   ├── third_party/         # Bibliotecas de terceiros
│   ├── views/              # Views da aplicação
│   │   ├── tema/           # Templates base
│   │   └── ...             # Views por módulo
│   └── vendor/             # Dependências do Composer
├── assets/                  # Arquivos estáticos
│   ├── css/                # Estilos CSS
│   ├── js/                 # Scripts JavaScript
│   ├── img/                # Imagens
│   ├── anexos/             # Anexos de OS
│   ├── arquivos/           # Arquivos gerais
│   └── userImage/          # Imagens de usuários
├── docker/                 # Configuração Docker
│   ├── docker-compose.yml
│   └── etc/                # Configurações dos containers
├── install/                # Sistema de instalação
├── updates/                # Scripts de atualização SQL
├── index.php               # Front controller
├── composer.json           # Dependências PHP
└── .env                    # Variáveis de ambiente
```

### Convenções de Nomenclatura

#### Controllers
- Nome em PascalCase: `Os.php`, `Clientes.php`, `Vendas.php`
- Herdam de `MY_Controller`
- Métodos públicos são ações: `index()`, `adicionar()`, `editar()`, `excluir()`

#### Models
- Nome em PascalCase com sufixo `_model`: `Os_model.php`, `Clientes_model.php`
- Herdam de `CI_Model`
- Métodos CRUD padrão: `get()`, `getById()`, `add()`, `edit()`, `delete()`

#### Views
- Estrutura por módulo: `os/os.php`, `clientes/clientes.php`
- Templates em `tema/`: `topo.php`, `menu.php`, `conteudo.php`, `rodape.php`
- Views carregadas dinamicamente via `$this->data['view']`

#### API Controllers
- Namespace: `api/v1/`
- Herdam de `REST_Controller`
- Métodos HTTP: `index_get()`, `index_post()`, `index_put()`, `index_delete()`

---

## Padrões Arquiteturais

### 1. Padrão MVC
- **Separação de responsabilidades**: Lógica de negócio (Model), apresentação (View), controle (Controller)
- **Baixo acoplamento**: Componentes independentes
- **Alta coesão**: Responsabilidades bem definidas

### 2. Padrão Repository (implícito)
- Models atuam como repositórios de dados
- Abstração da camada de banco de dados
- Métodos específicos por entidade

### 3. Padrão Template Method
- `MY_Controller::layout()` define estrutura de renderização
- Views específicas injetadas via `$this->data['view']`

### 4. Padrão Singleton
- CodeIgniter usa singleton para instâncias (database, session, etc.)
- Acesso via `$this->load->library()` ou `$this->load->model()`

### 5. Padrão Factory
- CodeIgniter usa factory para criar instâncias de classes
- Autoloading via Composer e CodeIgniter

### 6. Padrão Strategy
- Sistema de permissões permite diferentes estratégias de acesso
- Gateways de pagamento como estratégias intercambiáveis

---

## Fluxos Principais

### Fluxo de Autenticação Web

1. Usuário acessa sistema
2. `MY_Controller` verifica sessão
3. Se não autenticado → redireciona para `Login`
4. Login valida credenciais
5. Cria sessão com dados do usuário
6. Redireciona para dashboard

### Fluxo de Autenticação API

1. Cliente envia credenciais para `/api/v1/login`
2. API valida credenciais
3. Gera token JWT
4. Retorna token ao cliente
5. Cliente usa token no header `Authorization: Bearer <token>`
6. `REST_Controller` valida token em cada requisição

### Fluxo de Criação de OS

1. Controller `Os::adicionar()` recebe requisição
2. Valida permissão do usuário
3. Valida dados do formulário
4. Model `Os_model::add()` processa dados
5. Insere OS no banco
6. Cria relacionamentos (produtos, serviços)
7. Envia notificações (se configurado)
8. Retorna sucesso/erro

### Fluxo de Geração de PDF

1. Controller recebe requisição de impressão
2. Model busca dados completos
3. Biblioteca mPDF gera PDF
4. PDF retornado ao navegador ou salvo

### Fluxo de Pagamento

1. Usuário gera cobrança na OS/Venda
2. Sistema escolhe gateway configurado
3. SDK do gateway cria transação
4. Retorna link/boleto/QR Code
5. Sistema salva referência da cobrança
6. Webhook (se disponível) atualiza status

---

## Integrações

### Gateways de Pagamento

#### Mercado Pago
- SDK: `mercadopago/dx-php`
- Funcionalidades: Pagamentos online, boletos, cartões
- Configuração via variáveis de ambiente

#### EFI (Gerencianet)
- SDK: `efipay/sdk-php-apis-efi`
- Funcionalidades: Boletos, PIX, cartões
- Substituiu antiga integração Gerencianet

#### Asaas
- SDK: `codephix/asaas-sdk`
- Funcionalidades: Boletos, PIX, cartões, links de pagamento
- Configuração via painel administrativo

#### PIX
- Biblioteca: `piggly/php-pix`
- Funcionalidades: Geração de QR Code PIX estático
- Integrado em impressões de OS e Vendas

### APIs Externas

#### Receita Federal (CNPJ)
- Busca automática de dados de empresas
- Preenchimento automático de formulários

#### ViaCEP
- Busca de endereços por CEP
- Preenchimento automático de campos

### Comunicação

#### Email
- SMTP configurável
- Fila de emails para processamento assíncrono
- Templates de email por tipo de notificação

#### WhatsApp
- Links para envio de mensagens
- Integração com WhatsApp Web e Mobile
- Notificações automáticas

---

## Configurações e Ambiente

### Variáveis de Ambiente (.env)

#### Aplicação
- `APP_ENVIRONMENT` - Ambiente (development/production/pre_installation)
- `APP_NAME` - Nome da aplicação
- `APP_SUBNAME` - Subtítulo
- `APP_BASEURL` - URL base do sistema
- `APP_TIMEZONE` - Fuso horário
- `APP_CHARSET` - Charset (UTF-8)
- `APP_ENCRYPTION_KEY` - Chave de criptografia

#### Banco de Dados
- `DB_DSN` - Data Source Name (opcional)
- `DB_HOSTNAME` - Host do banco
- `DB_USERNAME` - Usuário
- `DB_PASSWORD` - Senha
- `DB_DATABASE` - Nome do banco
- `DB_DRIVER` - Driver (mysqli)
- `DB_PREFIX` - Prefixo de tabelas
- `DB_CHARSET` - Charset do banco
- `DB_COLLATION` - Collation

#### Sessão
- `APP_SESS_DRIVER` - Driver de sessão (database/files)
- `APP_SESS_COOKIE_NAME` - Nome do cookie
- `APP_SESS_EXPIRATION` - Tempo de expiração (segundos)
- `APP_SESS_SAVE_PATH` - Caminho/tabela de sessão
- `APP_SESS_MATCH_IP` - Validar IP na sessão
- `APP_SESS_TIME_TO_UPDATE` - Intervalo de regeneração

#### Segurança
- `APP_CSRF_PROTECTION` - Habilitar proteção CSRF
- `APP_CSRF_TOKEN_NAME` - Nome do token CSRF
- `APP_CSRF_COOKIE_NAME` - Nome do cookie CSRF
- `APP_CSRF_EXPIRE` - Expiração do token
- `APP_CSRF_REGENERATE` - Regenerar token a cada requisição
- `GLOBAL_XSS_FILTERING` - Filtro XSS global

#### API
- `API_ENABLED` - Habilitar/desabilitar API
- Configurações JWT em `application/config/jwt.php`

#### Outros
- `APP_COOKIE_PREFIX` - Prefixo de cookies
- `APP_COOKIE_DOMAIN` - Domínio dos cookies
- `APP_COOKIE_PATH` - Caminho dos cookies
- `APP_COOKIE_SECURE` - Cookies apenas HTTPS
- `APP_COOKIE_HTTPONLY` - Cookies sem acesso JavaScript
- `APP_COMPRESS_OUTPUT` - Compressão GZIP
- `APP_PROXY_IPS` - IPs de proxy confiáveis
- `WHOOPS_ERROR_PAGE_ENABLED` - Páginas de erro Whoops

### Configurações do Sistema (Banco de Dados)

Armazenadas na tabela `configuracoes`:
- `app_name` - Nome do sistema
- `app_theme` - Tema visual
- `per_page` - Itens por página
- `os_notification` - Configuração de notificações
- `control_estoque` - Controle de estoque
- `notifica_whats` - Texto de notificação WhatsApp
- `control_baixa` - Controle de baixa financeira
- `control_editos` - Permitir edição de OS faturadas
- `control_datatable` - Usar DataTables
- `pix_key` - Chave PIX
- E outras configurações específicas

---

## Segurança

### Medidas Implementadas

#### Autenticação e Autorização
- Sessões baseadas em banco de dados
- Hash de senhas com `password_hash()` e `password_verify()`
- Tokens JWT para API com expiração
- Validação de permissões em cada ação
- Data de expiração de acesso para usuários

#### Proteção de Dados
- CSRF Protection habilitado
- XSS Filtering global
- Query Builder do CodeIgniter (proteção SQL Injection)
- Validação de inputs via Form Validation
- Sanitização de dados de entrada

#### Sessões
- Sessões em banco de dados (mais seguro que arquivos)
- Regeneração periódica de ID de sessão
- Validação opcional de IP
- Cookies HttpOnly e Secure (configurável)

#### API
- Autenticação via JWT
- Validação de token em cada requisição
- Headers CORS configuráveis
- Rate limiting (via configuração do servidor)

### Boas Práticas Aplicadas
- Senhas nunca em texto plano
- Tokens com expiração
- Validação de permissões em múltiplas camadas
- Logs de auditoria de ações críticas
- Proteção contra CSRF em formulários
- Sanitização de outputs

---

## Banco de Dados

### Estrutura

#### Versionamento
- Sistema de **Migrations** do CodeIgniter
- Arquivos em `application/database/migrations/`
- Nomenclatura: `YYYYMMDDHHMMSS_nome_migration.php`
- Execução via interface web ou CLI

#### Tabelas Principais

**Gestão de Usuários**
- `usuarios` - Usuários do sistema
- `permissoes` - Grupos de permissões
- `ci_sessions` - Sessões ativas

**Gestão de Clientes**
- `clientes` - Cadastro de clientes/fornecedores
- `garantias` - Termos de garantia

**Ordens de Serviço**
- `os` - Ordens de serviço
- `produtos_os` - Produtos vinculados à OS
- `servicos_os` - Serviços vinculados à OS
- `anotacoes_os` - Anotações da OS
- `anexos` - Anexos de OS

**Vendas**
- `vendas` - Vendas
- `produtos_vendas` - Produtos da venda
- `servicos_vendas` - Serviços da venda

**Produtos e Serviços**
- `produtos` - Cadastro de produtos
- `servicos` - Cadastro de serviços

**Financeiro**
- `lancamentos` - Lançamentos financeiros
- `cobrancas` - Cobranças geradas

**Sistema**
- `configuracoes` - Configurações do sistema
- `auditoria` - Log de auditoria
- `emitente` - Dados do emitente

### Relacionamentos

- OS → Cliente (N:1)
- OS → Usuário/Técnico (N:1)
- OS → Garantia (N:1)
- OS → Produtos (1:N via `produtos_os`)
- OS → Serviços (1:N via `servicos_os`)
- Venda → Cliente (N:1)
- Venda → Produtos (1:N via `produtos_vendas`)
- Lançamento → Cliente (N:1)
- Lançamento → OS/Venda (N:1, opcional)

### Convenções

- Chaves primárias: `idNomeTabela` (ex: `idOs`, `idClientes`)
- Chaves estrangeiras: `tabela_id` (ex: `clientes_id`, `usuarios_id`)
- Soft deletes não implementado (deletes físicos)
- Timestamps: `dataCadastro`, `dataAlteracao` (quando aplicável)

---

## Módulos Principais

### 1. Ordens de Serviço (OS)
- CRUD completo
- Status: Aberta, Em Andamento, Aguardando Peças, Finalizada, Faturada, Cancelada, Aprovada, Orçamento
- Produtos e serviços vinculados
- Anexos organizados por data
- Anotações
- Garantias
- Descontos
- Impressão (PDF e térmica)
- QR Code PIX

### 2. Clientes/Fornecedores
- CRUD completo
- Busca por CNPJ (Receita Federal)
- Busca por CEP (ViaCEP)
- Tipo: Cliente ou Fornecedor
- Histórico de OS e Vendas

### 3. Produtos
- CRUD completo
- Controle de estoque
- Código de barras
- Múltiplas unidades de medida
- Margem de lucro
- Relatórios de estoque mínimo

### 4. Serviços
- CRUD completo
- Preços configuráveis
- Vinculação a OS e Vendas

### 5. Vendas
- CRUD completo
- Similar à OS mas para vendas diretas
- Status: Aberto, Em Andamento, Finalizada, Faturada, Cancelada, Aprovada, Orçamento
- Produtos e serviços
- Descontos
- Impressão

### 6. Financeiro
- Lançamentos (receitas/despesas)
- Parcelamento
- Controle de baixa
- Relatórios (PDF e XLSX)
- Filtros avançados
- Gráficos e estatísticas

### 7. Cobranças
- Geração de boletos
- Links de pagamento
- Integração com gateways
- Status de pagamento
- Reenvio de cobranças

### 8. Relatórios
- OS (rápido e customizado)
- Vendas (rápido e customizado)
- Financeiro
- Clientes
- Produtos (SKU, estoque mínimo)
- Exportação em PDF e XLSX

### 9. Usuários e Permissões
- CRUD de usuários
- Grupos de permissões
- Permissões por módulo e ação
- Data de expiração de acesso
- Foto de perfil

### 10. Área do Cliente
- Portal web para clientes
- Login e recuperação de senha
- Visualização de OS e Vendas
- Histórico de compras
- Cobranças pendentes
- API REST para app mobile

### 11. API REST
- Endpoints para todos os módulos
- Autenticação JWT
- Versão 1 (v1)
- Endpoints específicos para área do cliente
- Documentação via código

### 12. Auditoria
- Log de todas as ações críticas
- Rastreamento de alterações
- Histórico de acesso

---

## Processamento Assíncrono

### Cron Jobs

#### Envio de Emails
- Processar fila de emails: `*/2 * * * * php index.php email/process`
- Reenviar emails com falha: `*/5 * * * * php index.php email/retry`

### Fila de Emails
- Emails não enviados ficam em fila
- Processamento via CLI
- Retry automático de falhas

---

## Deploy e Manutenção

### Ambientes

#### Development
- Debug habilitado
- Logs detalhados
- Whoops para erros

#### Production
- Debug desabilitado
- Logs mínimos
- Erros genéricos

### Atualização

#### Via Interface
- Botão "Atualizar Mapos" em Configurações
- Download automático do GitHub
- Preserva configurações

#### Manual
1. Backup de arquivos e banco
2. Substituir arquivos
3. Executar `composer install --no-dev`
4. Executar migrations via interface ou CLI
5. Restaurar backups de assets

### Backup
- Backup de banco via interface
- Backup manual de pastas: `assets/anexos`, `assets/arquivos`, `assets/userImage`
- Backup de `.env`

---

## Performance

### Otimizações
- Query Builder com JOINs otimizados
- Paginação em listagens
- Cache de configurações (carregadas uma vez)
- Compressão GZIP (opcional)
- Minificação de assets (via build)

### Escalabilidade
- Arquitetura monolítica (vertical scaling)
- Banco de dados relacional
- Sessões em banco (permite múltiplos servidores com mesmo banco)
- Assets estáticos servidos diretamente

---

## Observações Finais

### Pontos Fortes da Arquitetura
- Separação clara de responsabilidades (MVC)
- Reutilização de código (MY_Controller, helpers)
- Extensibilidade (fácil adicionar módulos)
- Segurança em múltiplas camadas
- API REST completa
- Sistema de permissões flexível

### Considerações
- Arquitetura monolítica (tudo em um projeto)
- CodeIgniter 3 (versão legada, considerar migração para CI4)
- Sem cache de queries (depende do banco)
- Processamento síncrono (exceto emails)

### Tecnologias Complementares Recomendadas
- Redis/Memcached para cache
- Queue system (RabbitMQ, Redis Queue) para processamento assíncrono
- Elasticsearch para buscas avançadas
- CDN para assets estáticos
- Load balancer para múltiplas instâncias

---

**Documento gerado para referência arquitetural e tecnológica do MapOS**
**Última atualização baseada na versão 4.52.0**

