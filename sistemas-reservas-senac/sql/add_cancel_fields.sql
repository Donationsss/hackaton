-- Adicionar status 'cancelado' ao ENUM
ALTER TABLE reservas MODIFY COLUMN status ENUM('livre','proposta','reservado','cancelado') NOT NULL DEFAULT 'livre';

-- Adicionar campo para justificativa de cancelamento
ALTER TABLE reservas ADD COLUMN IF NOT EXISTS cancel_reason TEXT NULL;

