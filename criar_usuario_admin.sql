-- Script SQL para criar usuário admin manualmente
-- Execute no phpMyAdmin ou via linha de comando MySQL

-- Verificar se o usuário já existe
SELECT * FROM usuarios WHERE email = 'admin@admin.com';

-- Se não existir, criar o usuário
-- IMPORTANTE: A senha é o hash de '123456'
INSERT INTO usuarios (
    nome,
    rg,
    cpf,
    cep,
    rua,
    numero,
    bairro,
    cidade,
    estado,
    email,
    senha,
    telefone,
    celular,
    situacao,
    dataCadastro,
    permissoes_id,
    dataExpiracao
) VALUES (
    'Admin',
    'MG-25.502.560',
    '517.565.356-39',
    '01024-900',
    'R. Cantareira',
    '306',
    'Centro Histórico de São Paulo',
    'São Paulo',
    'SP',
    'admin@admin.com',
    '$2y$10$lAW0AXb0JLZxR0yDdfcBcu3BN9c2AXKKjKTdug7Or0pr6cSGtgyGO',
    '0000-0000',
    '',
    1,
    CURDATE(),
    1,
    '2030-01-01'
)
ON DUPLICATE KEY UPDATE email = email;

-- Verificar se foi criado
SELECT idUsuarios, nome, email, situacao FROM usuarios WHERE email = 'admin@admin.com';

