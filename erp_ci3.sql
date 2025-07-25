-- Cria o banco
CREATE DATABASE IF NOT EXISTS erp_ci3
CHARACTER SET utf8
COLLATE utf8_general_ci;

USE erp_ci3;

-- Tabela produtos (sem estoque integrado, conforme regra)
CREATE TABLE IF NOT EXISTS produtos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(255) NOT NULL,
    descricao TEXT NULL,
    preco DECIMAL(10,2) NOT NULL,
    ativo BOOLEAN DEFAULT TRUE,
    categoria VARCHAR(100) NULL,
    variacoes TEXT NULL COMMENT 'JSON com variações do produto',
    criado_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    atualizado_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Tabela estoque (separada conforme regra)
CREATE TABLE IF NOT EXISTS estoque (
    id INT AUTO_INCREMENT PRIMARY KEY,
    produto_id INT NOT NULL,
    variacao VARCHAR(255) NULL COMMENT 'Variação específica do produto',
    quantidade INT DEFAULT 0,
    quantidade_minima INT DEFAULT 5,
    criado_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    atualizado_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (produto_id) REFERENCES produtos(id) ON DELETE CASCADE,
    UNIQUE KEY unique_produto_variacao (produto_id, variacao)
);

-- Tabela cupons (atualizada com campo de validade)
CREATE TABLE IF NOT EXISTS cupons (
    id INT AUTO_INCREMENT PRIMARY KEY,
    codigo VARCHAR(50) NOT NULL UNIQUE,
    tipo ENUM('percentual', 'fixo') NOT NULL,
    valor DECIMAL(10,2) NOT NULL,
    validade DATE NULL COMMENT 'Data de validade (NULL = sem validade)',
    minimo DECIMAL(10,2) DEFAULT 0,
    ativo BOOLEAN DEFAULT TRUE,
    criado_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Tabela usuarios para autenticação OAuth
CREATE TABLE IF NOT EXISTS usuarios (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(255) NOT NULL,
    email VARCHAR(255) NOT NULL UNIQUE,
    google_id VARCHAR(255) NULL UNIQUE,
    foto VARCHAR(500) NULL,
    criado_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    atualizado_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Tabela pedidos (atualizada com campos para e-mail e webhook)
CREATE TABLE IF NOT EXISTS pedidos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    usuario_id INT NULL,
    cupom_id INT NULL,
    email_cliente VARCHAR(255) NULL COMMENT 'E-mail para confirmação do pedido',
    cep VARCHAR(10) NULL COMMENT 'CEP de entrega',
    endereco_completo TEXT NULL COMMENT 'Endereço completo de entrega',
    cidade VARCHAR(100) NULL COMMENT 'Cidade de entrega',
    estado VARCHAR(2) NULL COMMENT 'Estado de entrega (UF)',
    subtotal DECIMAL(10,2) NOT NULL DEFAULT 0,
    frete DECIMAL(10,2) NOT NULL DEFAULT 0,
    total DECIMAL(10,2) NOT NULL DEFAULT 0,
    status ENUM('pendente', 'processando', 'enviado', 'entregue', 'cancelado') DEFAULT 'pendente' COMMENT 'Status atualizado via webhook',
    data_pedido DATETIME NULL COMMENT 'Data e hora do pedido',
    criado_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    atualizado_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (cupom_id) REFERENCES cupons(id),
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id)
);

-- Tabela itens_pedido (com variação)
CREATE TABLE IF NOT EXISTS itens_pedido (
    id INT AUTO_INCREMENT PRIMARY KEY,
    pedido_id INT NOT NULL,
    produto_id INT NOT NULL,
    variacao VARCHAR(255) NULL COMMENT 'JSON com variação específica do produto',
    quantidade INT NOT NULL,
    preco_unitario DECIMAL(10,2) NOT NULL,
    subtotal DECIMAL(10,2) NOT NULL,
    FOREIGN KEY (pedido_id) REFERENCES pedidos(id) ON DELETE CASCADE,
    FOREIGN KEY (produto_id) REFERENCES produtos(id),
    INDEX idx_pedido (pedido_id)
);

-- Índices para melhor performance
CREATE INDEX idx_produtos_ativo ON produtos(ativo);
CREATE INDEX idx_produtos_categoria ON produtos(categoria);
CREATE INDEX idx_usuarios_email ON usuarios(email);
CREATE INDEX idx_usuarios_google_id ON usuarios(google_id);
CREATE INDEX idx_cupons_codigo ON cupons(codigo);
CREATE INDEX idx_pedidos_usuario ON pedidos(usuario_id);

-- Dados de exemplo (atualizados)
INSERT INTO produtos (nome, descricao, preco, ativo, categoria, variacoes) VALUES
('Camiseta Básica', 'Camiseta 100% algodão', 29.90, TRUE, 'Roupas', '{"cor":["azul","branco","preto"],"tamanho":["P","M","G","GG"]}'),
('Tênis Esportivo', 'Tênis para corrida e caminhada', 159.90, TRUE, 'Calçados', '{"cor":["preto","branco"],"tamanho":["38","39","40","41","42","43"]}'),
('Caneca Personalizada', 'Caneca de porcelana 300ml', 19.90, TRUE, 'Casa', '{"cor":["branca","preta"],"estampa":["lisa","personalizada"]}');

-- Estoque inicial para os produtos
INSERT INTO estoque (produto_id, variacao, quantidade, quantidade_minima) VALUES
(1, 'cor:azul,tamanho:M', 20, 5),
(1, 'cor:branco,tamanho:M', 15, 5),
(1, 'cor:preto,tamanho:G', 25, 5),
(2, 'cor:preto,tamanho:42', 10, 3),
(2, 'cor:branco,tamanho:40', 8, 3),
(3, 'cor:branca,estampa:lisa', 50, 10),
(3, 'cor:preta,estampa:personalizada', 30, 10);

-- Cupons de exemplo (alguns com validade, outros sem)
INSERT INTO cupons (codigo, tipo, valor, validade, minimo, ativo) VALUES
('WELCOME10', 'percentual', 10.00, '2025-12-31', 50.00, TRUE),
('FRETE20', 'fixo', 20.00, '2025-12-31', 100.00, TRUE),
('LIQUIDACAO', 'percentual', 50.00, NULL, 0.00, TRUE),
('PROMO15', 'percentual', 15.00, '2025-08-31', 80.00, TRUE),
('DESCONTO5', 'fixo', 5.00, NULL, 20.00, TRUE);

-- ========================================
-- ATUALIZAÇÕES PARA E-MAIL E WEBHOOK
-- ========================================

-- Script para atualizar banco existente (execute apenas se já tiver o banco criado):
-- 
-- ALTER TABLE pedidos 
-- ADD COLUMN IF NOT EXISTS email_cliente VARCHAR(255) NULL AFTER cupom_id,
-- ADD COLUMN IF NOT EXISTS cep VARCHAR(10) NULL AFTER email_cliente,
-- ADD COLUMN IF NOT EXISTS endereco_completo TEXT NULL AFTER cep,
-- ADD COLUMN IF NOT EXISTS cidade VARCHAR(100) NULL AFTER endereco_completo,
-- ADD COLUMN IF NOT EXISTS estado VARCHAR(2) NULL AFTER cidade,
-- MODIFY COLUMN status ENUM('pendente', 'processando', 'enviado', 'entregue', 'cancelado') DEFAULT 'pendente',
-- ADD COLUMN IF NOT EXISTS data_pedido DATETIME NULL AFTER status;
--
-- ALTER TABLE cupons 
-- MODIFY COLUMN validade DATE NULL COMMENT 'Data de validade (NULL = sem validade)';

-- ========================================
-- FUNCIONALIDADES IMPLEMENTADAS
-- ========================================
-- 
-- ✅ Sistema de E-mail:
--    - E-mail de confirmação ao finalizar pedido
--    - E-mail de atualização de status via webhook
--    - Templates HTML responsivos
--    - Campo email_cliente obrigatório no checkout
-- 
-- ✅ Webhook de Status:
--    - Endpoint: POST /webhook/atualizar_pedido
--    - Token de segurança configurado
--    - Status cancelado remove o pedido
--    - Outros status atualizam o campo status
--    - Log completo de operações
-- 
-- ✅ Sistema de Cupons:
--    - Cupons com e sem data de validade
--    - Aplicação no checkout com AJAX
--    - Validação de expiração
--    - Interface administrativa completa
-- 
-- ✅ Gestão de Estoque:
--    - Tabela separada conforme regra de negócio
--    - Controle por produto e variação
--    - Baixa automática no estoque ao finalizar pedido
-- 
-- Data de criação: 24 de Julho de 2025
