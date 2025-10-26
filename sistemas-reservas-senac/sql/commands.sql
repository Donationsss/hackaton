-- Crie o banco e tabelas essenciais
-- Execute este arquivo no phpMyAdmin (XAMPP) ou via cliente MySQL

CREATE DATABASE IF NOT EXISTS reservas_senac CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE reservas_senac;

-- Tabela de papéis/categorias
CREATE TABLE IF NOT EXISTS roles (
  id INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(50) NOT NULL UNIQUE,
  description VARCHAR(255) NULL
) ENGINE=InnoDB;

-- Tabela de usuários
CREATE TABLE IF NOT EXISTS users (
  id INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(100) NOT NULL,
  email VARCHAR(150) NOT NULL UNIQUE,
  password_hash VARCHAR(255) NOT NULL,
  role_id INT NOT NULL,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT fk_users_role FOREIGN KEY (role_id) REFERENCES roles(id)
    ON UPDATE CASCADE ON DELETE RESTRICT
) ENGINE=InnoDB;

-- Tabela de reservas (slots)
CREATE TABLE IF NOT EXISTS reservas (
  id INT AUTO_INCREMENT PRIMARY KEY,
  date DATE NOT NULL,
  time_start TIME NOT NULL,
  time_end TIME NOT NULL,
  status ENUM('livre','proposta','reservado') NOT NULL DEFAULT 'livre',
  created_by INT NULL,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  approved_by INT NULL,
  approved_at DATETIME NULL,
  CONSTRAINT fk_reservas_created_by FOREIGN KEY (created_by) REFERENCES users(id)
    ON UPDATE CASCADE ON DELETE SET NULL,
  CONSTRAINT fk_reservas_approved_by FOREIGN KEY (approved_by) REFERENCES users(id)
    ON UPDATE CASCADE ON DELETE SET NULL
) ENGINE=InnoDB;

-- Índices úteis
CREATE INDEX IF NOT EXISTS idx_reservas_date ON reservas(date);
CREATE INDEX IF NOT EXISTS idx_reservas_status ON reservas(status);

-- Papéis básicos
INSERT IGNORE INTO roles (name, description) VALUES
  ('administrador', 'Acesso total ao dashboard e aprovação/rejeição de reservas'),
  ('visualizador', 'Pode visualizar slots livres');

-- Opcional: slot de exemplo (livre hoje, 09:00-10:00)
INSERT INTO reservas (date, time_start, time_end, status)
VALUES (CURDATE(), '09:00:00', '10:00:00', 'livre');

-- =============== NOVOS RECURSOS ===============
-- Tabela de espaços/salas
CREATE TABLE IF NOT EXISTS spaces (
  id INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(100) NOT NULL,
  type ENUM('sala','laboratorio','auditorio','sala_reuniao','outro') NOT NULL DEFAULT 'sala',
  capacity INT NULL,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- Coluna space_id em reservas
ALTER TABLE reservas ADD COLUMN IF NOT EXISTS space_id INT NULL;

-- Chave estrangeira (sem IF NOT EXISTS; execute uma única vez)
ALTER TABLE reservas
  ADD CONSTRAINT fk_reservas_space FOREIGN KEY (space_id) REFERENCES spaces(id)
    ON UPDATE CASCADE ON DELETE SET NULL;

-- Espaços de exemplo
INSERT INTO spaces (name, type, capacity) VALUES
  ('Auditório Principal', 'auditorio', 200),
  ('Laboratório de Informática 1', 'laboratorio', 40),
  ('Sala de Reuniões Executiva', 'sala_reuniao', 20),
  ('Sala de Aula 101', 'sala', 50)
ON DUPLICATE KEY UPDATE name = VALUES(name);

-- Ajustar slot de exemplo para associar a um espaço existente
UPDATE reservas SET space_id = (SELECT id FROM spaces ORDER BY id LIMIT 1) WHERE space_id IS NULL;

-- Campos opcionais para propostas (título e observações)
ALTER TABLE reservas ADD COLUMN IF NOT EXISTS request_title VARCHAR(150) NULL;
ALTER TABLE reservas ADD COLUMN IF NOT EXISTS request_note TEXT NULL;
ALTER TABLE reservas ADD COLUMN IF NOT EXISTS requester_name VARCHAR(150) NULL;
