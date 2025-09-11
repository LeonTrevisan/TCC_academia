-- Criação do banco de dados GymPro
CREATE DATABASE IF NOT EXISTS gympro;
USE gympro;

-- Tabela de usuários do sistema
CREATE TABLE usuarios (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    name VARCHAR(100) NOT NULL,
    role ENUM('admin', 'employee') NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Tabela de planos
CREATE TABLE planos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(50) NOT NULL,
    valor DECIMAL(8,2) NOT NULL,
    duracao_meses INT NOT NULL,
    descricao TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Tabela de alunos
CREATE TABLE alunos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(100) NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    telefone VARCHAR(20) NOT NULL,
    cpf VARCHAR(14) UNIQUE NOT NULL,
    data_nascimento DATE NOT NULL,
    plano_id INT,
    status ENUM('active', 'inactive') DEFAULT 'active',
    data_matricula DATE NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (plano_id) REFERENCES planos(id)
);

-- Tabela de categorias de equipamentos
CREATE TABLE categorias_equipamentos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(50) NOT NULL
);

-- Tabela de equipamentos
CREATE TABLE equipamentos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(100) NOT NULL,
    categoria_id INT,
    marca VARCHAR(50) NOT NULL,
    numero_serie VARCHAR(50) UNIQUE NOT NULL,
    data_aquisicao DATE NOT NULL,
    status ENUM('ok', 'maintenance', 'broken') DEFAULT 'ok',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (categoria_id) REFERENCES categorias_equipamentos(id)
);

-- Tabela de manutenções
CREATE TABLE manutencoes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    equipamento_id INT NOT NULL,
    tipo ENUM('preventiva', 'corretiva', 'emergencial') NOT NULL,
    data_agendada DATE NOT NULL,
    data_realizada DATE NULL,
    tecnico_responsavel VARCHAR(100) NOT NULL,
    descricao TEXT NOT NULL,
    status ENUM('agendado', 'em_andamento', 'concluido', 'cancelado') DEFAULT 'agendado',
    custo DECIMAL(8,2) NULL,
    observacoes TEXT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (equipamento_id) REFERENCES equipamentos(id)
);

-- Tabela de pagamentos
CREATE TABLE pagamentos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    aluno_id INT NOT NULL,
    plano_id INT NOT NULL,
    valor DECIMAL(8,2) NOT NULL,
    data_vencimento DATE NOT NULL,
    data_pagamento DATE NULL,
    status ENUM('pendente', 'pago', 'atrasado', 'cancelado') DEFAULT 'pendente',
    metodo_pagamento VARCHAR(50) NULL,
    observacoes TEXT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (aluno_id) REFERENCES alunos(id),
    FOREIGN KEY (plano_id) REFERENCES planos(id)
);

-- Inserção de dados iniciais

-- Usuários padrão
INSERT INTO usuarios (username, password, name, role) VALUES
('admin', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Administrador', 'admin'),
('funcionario', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Funcionário', 'employee');

-- Categorias de equipamentos
INSERT INTO categorias_equipamentos (nome) VALUES
('Cardio'),
('Musculação'),
('Funcional'),
('CrossFit'),
('Aeróbico');

-- Planos
INSERT INTO planos (nome, valor, duracao_meses, descricao) VALUES
('Mensal', 89.90, 1, 'Acesso completo à academia, uso de todos os equipamentos, aulas em grupo básicas, avaliação física mensal'),
('Trimestral', 249.90, 3, 'Tudo do plano mensal, aulas funcionais, desconto de 7%, suporte nutricional básico'),
('Semestral', 479.90, 6, 'Tudo do plano trimestral, personal trainer 2x/mês, desconto de 12%, acompanhamento completo'),
('Anual', 899.90, 12, 'Tudo dos outros planos, personal trainer ilimitado, desconto de 17%, aulas premium exclusivas');

-- Equipamentos de exemplo
INSERT INTO equipamentos (nome, categoria_id, marca, numero_serie, data_aquisicao, status) VALUES
('Esteira Elétrica', 1, 'TechnoGym', 'TG001234', '2024-01-15', 'ok'),
('Leg Press 45°', 2, 'Life Fitness', 'LF005678', '2023-08-20', 'maintenance'),
('Bicicleta Ergométrica', 1, 'Schwinn', 'SW009876', '2023-12-05', 'broken'),
('Supino Reto', 2, 'Hammer Strength', 'HS012345', '2024-03-10', 'ok'),
('Elíptico', 1, 'Precor', 'PC054321', '2024-02-20', 'ok');

-- Alunos de exemplo
INSERT INTO alunos (nome, email, telefone, cpf, data_nascimento, plano_id, data_matricula) VALUES
('João Silva', 'joao@email.com', '(11) 99999-1111', '123.456.789-10', '1990-05-15', 1, '2025-01-15'),
('Maria Santos', 'maria@email.com', '(11) 99999-2222', '987.654.321-00', '1988-08-22', 2, '2024-11-20'),
('Carlos Oliveira', 'carlos@email.com', '(11) 99999-3333', '456.789.123-45', '1985-12-10', 1, '2024-12-01'),
('Ana Costa', 'ana@email.com', '(11) 99999-4444', '789.123.456-78', '1992-03-08', 4, '2024-10-05'),
('Pedro Ferreira', 'pedro@email.com', '(11) 99999-5555', '321.654.987-21', '1987-11-30', 3, '2024-09-12');

-- Manutenções de exemplo
INSERT INTO manutencoes (equipamento_id, tipo, data_agendada, tecnico_responsavel, descricao, status) VALUES
(2, 'preventiva', '2025-09-15', 'João Técnico', 'Lubrificação e ajuste dos cabos', 'agendado'),
(3, 'corretiva', '2025-09-12', 'Maria Técnica', 'Substituição do display quebrado', 'em_andamento');

-- Pagamentos de exemplo
INSERT INTO pagamentos (aluno_id, plano_id, valor, data_vencimento, data_pagamento, status, metodo_pagamento) VALUES
(1, 1, 89.90, '2025-09-15', '2025-09-14', 'pago', 'Cartão de Crédito'),
(2, 2, 249.90, '2025-09-20', NULL, 'pendente', NULL),
(3, 1, 89.90, '2025-09-10', NULL, 'atrasado', NULL),
(4, 4, 899.90, '2025-10-05', '2025-09-30', 'pago', 'PIX'),
(5, 3, 479.90, '2025-09-25', NULL, 'pendente', NULL);

-- Views para relatórios

-- View de alunos com informações do plano
CREATE VIEW vw_alunos_completo AS
SELECT 
    a.id,
    a.nome,
    a.email,
    a.telefone,
    a.cpf,
    a.data_nascimento,
    a.status,
    a.data_matricula,
    p.nome as plano_nome,
    p.valor as plano_valor,
    p.duracao_meses
FROM alunos a
LEFT JOIN planos p ON a.plano_id = p.id;

-- View de equipamentos com categoria
CREATE VIEW vw_equipamentos_completo AS
SELECT 
    e.id,
    e.nome,
    c.nome as categoria_nome,
    e.marca,
    e.numero_serie,
    e.data_aquisicao,
    e.status
FROM equipamentos e
LEFT JOIN categorias_equipamentos c ON e.categoria_id = c.id;

-- View de manutenções com equipamento
CREATE VIEW vw_manutencoes_completo AS
SELECT 
    m.id,
    e.nome as equipamento_nome,
    m.tipo,
    m.data_agendada,
    m.data_realizada,
    m.tecnico_responsavel,
    m.descricao,
    m.status,
    m.custo,
    m.observacoes
FROM manutencoes m
JOIN equipamentos e ON m.equipamento_id = e.id;

-- View de pagamentos com informações do aluno e plano
CREATE VIEW vw_pagamentos_completo AS
SELECT 
    p.id,
    a.nome as aluno_nome,
    pl.nome as plano_nome,
    p.valor,
    p.data_vencimento,
    p.data_pagamento,
    p.status,
    p.metodo_pagamento,
    p.observacoes
FROM pagamentos p
JOIN alunos a ON p.aluno_id = a.id
JOIN planos pl ON p.plano_id = pl.id;

-- Índices para melhor performance
CREATE INDEX idx_alunos_email ON alunos(email);
CREATE INDEX idx_alunos_cpf ON alunos(cpf);
CREATE INDEX idx_alunos_status ON alunos(status);
CREATE INDEX idx_equipamentos_status ON equipamentos(status);
CREATE INDEX idx_manutencoes_status ON manutencoes(status);
CREATE INDEX idx_pagamentos_status ON pagamentos(status);
CREATE INDEX idx_pagamentos_vencimento ON pagamentos(data_vencimento);

-- Procedures para operações comuns

-- Procedure para criar pagamento automático ao matricular aluno
DELIMITER //
CREATE PROCEDURE sp_criar_pagamento_matricula(
    IN p_aluno_id INT,
    IN p_plano_id INT
)
BEGIN
    DECLARE v_valor DECIMAL(8,2);
    DECLARE v_duracao INT;
    
    -- Buscar valor do plano
    SELECT valor, duracao_meses INTO v_valor, v_duracao
    FROM planos WHERE id = p_plano_id;
    
    -- Criar pagamento com vencimento em 30 dias
    INSERT INTO pagamentos (aluno_id, plano_id, valor, data_vencimento, status)
    VALUES (p_aluno_id, p_plano_id, v_valor, DATE_ADD(CURDATE(), INTERVAL 30 DAY), 'pendente');
END //
DELIMITER ;

-- Procedure para atualizar status de pagamentos atrasados
DELIMITER //
CREATE PROCEDURE sp_atualizar_pagamentos_atrasados()
BEGIN
    UPDATE pagamentos 
    SET status = 'atrasado' 
    WHERE status = 'pendente' 
    AND data_vencimento < CURDATE();
END //
DELIMITER ;

-- Trigger para atualizar status do equipamento quando manutenção é concluída
DELIMITER //
CREATE TRIGGER tr_manutencao_concluida
AFTER UPDATE ON manutencoes
FOR EACH ROW
BEGIN
    IF NEW.status = 'concluido' AND OLD.status != 'concluido' THEN
        UPDATE equipamentos 
        SET status = 'ok' 
        WHERE id = NEW.equipamento_id;
    END IF;
END //
DELIMITER ;

-- Event para executar automaticamente a atualização de pagamentos atrasados
CREATE EVENT ev_atualizar_pagamentos_atrasados
ON SCHEDULE EVERY 1 DAY
STARTS CURRENT_DATE + INTERVAL 1 DAY
DO CALL sp_atualizar_pagamentos_atrasados();