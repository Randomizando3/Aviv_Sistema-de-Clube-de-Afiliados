-- 1) Log de webhooks (opcional, mas muito útil)
CREATE TABLE IF NOT EXISTS webhooks_log (
  id BIGINT AUTO_INCREMENT PRIMARY KEY,
  provider VARCHAR(40) NOT NULL,
  event VARCHAR(80) NOT NULL,
  signature TEXT NULL,
  payload JSON NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 2) Faturas (invoices) vinculadas às assinaturas
CREATE TABLE IF NOT EXISTS invoices (
  id BIGINT AUTO_INCREMENT PRIMARY KEY,
  subscription_id INT NULL,
  asaas_invoice_id VARCHAR(80) UNIQUE,
  value DECIMAL(10,2) NOT NULL DEFAULT 0,
  status VARCHAR(20) NOT NULL DEFAULT 'pending',
  due_date DATE NULL,
  paid_at DATETIME NULL,
  raw JSON NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  KEY sub_idx (subscription_id),
  KEY status_idx (status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 3) Campos Asaas nas assinaturas (apenas se faltarem)
-- MySQL 8+ aceita IF NOT EXISTS em ADD COLUMN? Nem sempre. Usamos checagem via information_schema:
SET @db := DATABASE();

-- asaas_customer_id
SET @sql := (
  SELECT IF(
    EXISTS(
      SELECT 1 FROM information_schema.COLUMNS
      WHERE TABLE_SCHEMA=@db AND TABLE_NAME='subscriptions' AND COLUMN_NAME='asaas_customer_id'
    ),
    'SELECT 1',
    'ALTER TABLE subscriptions ADD COLUMN asaas_customer_id VARCHAR(80) NULL AFTER status'
  )
);
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- asaas_subscription_id
SET @sql := (
  SELECT IF(
    EXISTS(
      SELECT 1 FROM information_schema.COLUMNS
      WHERE TABLE_SCHEMA=@db AND TABLE_NAME='subscriptions' AND COLUMN_NAME='asaas_subscription_id'
    ),
    'SELECT 1',
    'ALTER TABLE subscriptions ADD COLUMN asaas_subscription_id VARCHAR(80) NULL AFTER asaas_customer_id'
  )
);
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- 4) Índices úteis
SET @sql := (
  SELECT IF(
    EXISTS(
      SELECT 1 FROM information_schema.STATISTICS
      WHERE TABLE_SCHEMA=@db AND TABLE_NAME='subscriptions' AND INDEX_NAME='idx_sub_asaas'
    ),
    'SELECT 1',
    'CREATE INDEX idx_sub_asaas ON subscriptions (asaas_subscription_id)'
  )
);
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;
