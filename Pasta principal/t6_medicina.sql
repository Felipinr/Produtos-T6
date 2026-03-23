-- 1. Criação do Banco de Dados
CREATE DATABASE IF NOT EXISTS t6_medicina DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE t6_medicina;

-- 2. Tabela de Usuários (Alunos e Pessoas Normais)
CREATE TABLE IF NOT EXISTS usuarios (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(100) NOT NULL,
    cpf VARCHAR(14) UNIQUE NOT NULL, 
    email VARCHAR(100) UNIQUE NOT NULL,
    senha VARCHAR(255) NOT NULL,
    telefone VARCHAR(20),
    ra_matricula VARCHAR(20) UNIQUE,
    turma ENUM('T1', 'T2', 'T3', 'T4', 'T5', 'T6', 'T7', 'Nenhuma') DEFAULT 'Nenhuma',
    -- LÓGICA AUTOMÁTICA: Se não tem turma, é 'externo' (pessoa normal). Se tem turma, é 'aluno'.
    tipo VARCHAR(10) GENERATED ALWAYS AS (IF(turma = 'Nenhuma', 'externo', 'aluno')) STORED,
    criado_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- 3. NOVA Tabela de Administradores (Painel ADM)
-- Apenas os utilizadores que estiverem nesta tabela terão acesso ao painel de gestão
CREATE TABLE IF NOT EXISTS administradores (
    id INT AUTO_INCREMENT PRIMARY KEY,
    usuario_id INT UNIQUE NOT NULL, -- UNIQUE garante que um utilizador não seja duplicado aqui
    data_promocao TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_admin_usuario FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- 4. Tabela de Produtos/Eventos
CREATE TABLE IF NOT EXISTS produtos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(100) NOT NULL,
    descricao TEXT,
    preco_externo DECIMAL(10,2) NOT NULL, -- Preço para pessoas normais (sem turma)
    preco_aluno DECIMAL(10,2) NOT NULL,   -- Preço para alunos (com turma)
    estoque INT DEFAULT 0,
    categoria ENUM('vestuario', 'caneca', 'ingresso', 'outro') NOT NULL,
    ativo BOOLEAN DEFAULT TRUE,
    imagem_url VARCHAR(255),
    criado_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- 5. Tabela de Vendas (O Pedido finalizado)
CREATE TABLE IF NOT EXISTS vendas (
    id INT AUTO_INCREMENT PRIMARY KEY,
    usuario_id INT,
    total DECIMAL(10,2) NOT NULL,
    status ENUM('pendente', 'pago', 'cancelado') DEFAULT 'pendente',
    metodo_pagamento VARCHAR(20),
    id_transacao_gateway VARCHAR(100),
    data_venda TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_usuario_venda FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE SET NULL
) ENGINE=InnoDB;

-- 6. Tabela de Itens da Venda (Para suportar a compra de múltiplos produtos)
CREATE TABLE IF NOT EXISTS itens_venda (
    id INT AUTO_INCREMENT PRIMARY KEY,
    venda_id INT,
    produto_id INT,
    quantidade INT NOT NULL,
    preco_unitario DECIMAL(10,2) NOT NULL,
    tamanho VARCHAR(10),
    CONSTRAINT fk_venda_item FOREIGN KEY (venda_id) REFERENCES vendas(id) ON DELETE CASCADE,
    CONSTRAINT fk_produto_item FOREIGN KEY (produto_id) REFERENCES produtos(id)
) ENGINE=InnoDB;

-- 7. Inserção do TEU Usuário e promoção a Administrador
-- ATENÇÃO: Altera os valores abaixo para os teus dados reais antes de executar!
-- Passo A: Cria a tua conta na tabela de utilizadores
INSERT INTO usuarios (nome, cpf, email, senha, turma) 
VALUES (
    'Felipe Matos',           -- Coloca o teu nome
    '069.790.521-79',            -- Coloca o teu CPF
    'felipematosduarte2@gmail.com',     -- Coloca o teu email real
    'felipe11353a',         -- Coloca a tua senha (depois no PHP usaremos criptografia)
    'T6'                         -- A tua turma
)
ON DUPLICATE KEY UPDATE nome='O Teu Nome Aqui';

-- Passo B: Coloca a tua conta na tabela de administradores usando o teu email
INSERT INTO administradores (usuario_id) 
SELECT id FROM usuarios WHERE email = 'felipematosduarte2@gmail.com' -- Lembra-te de colocar o mesmo email aqui
ON DUPLICATE KEY UPDATE usuario_id=usuario_id;