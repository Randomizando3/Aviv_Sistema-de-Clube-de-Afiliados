-- ===== Fase 2 (compatível com MySQL 5.7+/MariaDB) =====

-- Tabela de planos (garante a existência)
CREATE TABLE IF NOT EXISTS plans (
  id VARCHAR(50) PRIMARY KEY,
  name VARCHAR(120) NOT NULL,
  price_monthly DECIMAL(10,2) DEFAULT 0,
  price_yearly  DECIMAL(10,2) DEFAULT 0,
  status ENUM('active','inactive') DEFAULT 'active',
  sort_order INT DEFAULT 0,
  created_at DATETIME DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Adiciona colunas que possam faltar (sem IF NOT EXISTS)
SET @db := DATABASE();

-- price_monthly
SET @sql := IF(
  (SELECT COUNT(*) FROM information_schema.COLUMNS
   WHERE TABLE_SCHEMA=@db AND TABLE_NAME='plans' AND COLUMN_NAME='price_monthly') = 0,
  'ALTER TABLE plans ADD COLUMN price_monthly DECIMAL(10,2) DEFAULT 0',
  'SELECT 1'
);
PREPARE s FROM @sql; EXECUTE s; DEALLOCATE PREPARE s;

-- price_yearly
SET @sql := IF(
  (SELECT COUNT(*) FROM information_schema.COLUMNS
   WHERE TABLE_SCHEMA=@db AND TABLE_NAME='plans' AND COLUMN_NAME='price_yearly') = 0,
  'ALTER TABLE plans ADD COLUMN price_yearly DECIMAL(10,2) DEFAULT 0',
  'SELECT 1'
);
PREPARE s FROM @sql; EXECUTE s; DEALLOCATE PREPARE s;

-- status
SET @sql := IF(
  (SELECT COUNT(*) FROM information_schema.COLUMNS
   WHERE TABLE_SCHEMA=@db AND TABLE_NAME='plans' AND COLUMN_NAME='status') = 0,
  "ALTER TABLE plans ADD COLUMN status ENUM('active','inactive') DEFAULT 'active'",
  'SELECT 1'
);
PREPARE s FROM @sql; EXECUTE s; DEALLOCATE PREPARE s;

-- sort_order
SET @sql := IF(
  (SELECT COUNT(*) FROM information_schema.COLUMNS
   WHERE TABLE_SCHEMA=@db AND TABLE_NAME='plans' AND COLUMN_NAME='sort_order') = 0,
  'ALTER TABLE plans ADD COLUMN sort_order INT DEFAULT 0',
  'SELECT 1'
);
PREPARE s FROM @sql; EXECUTE s; DEALLOCATE PREPARE s;

-- Semeia planos básicos se não existirem
INSERT INTO plans (id,name,price_monthly,price_yearly,status,sort_order)
SELECT 'start','Start',29.90,299.00,'active',1
WHERE NOT EXISTS (SELECT 1 FROM plans WHERE id='start');

INSERT INTO plans (id,name,price_monthly,price_yearly,status,sort_order)
SELECT 'plus','Plus',59.90,599.00,'active',2
WHERE NOT EXISTS (SELECT 1 FROM plans WHERE id='plus');

INSERT INTO plans (id,name,price_monthly,price_yearly,status,sort_order)
SELECT 'prime','Prime',99.90,999.00,'active',3
WHERE NOT EXISTS (SELECT 1 FROM plans WHERE id='prime');

-- ===== Tabela de benefícios =====
CREATE TABLE IF NOT EXISTS benefits (
  id INT AUTO_INCREMENT PRIMARY KEY,
  title VARCHAR(150) NOT NULL,
  partner VARCHAR(120) DEFAULT NULL,
  type ENUM('coupon','link','service') NOT NULL DEFAULT 'coupon',
  code VARCHAR(80) DEFAULT NULL,
  link VARCHAR(255) DEFAULT NULL,
  valid_until DATE DEFAULT NULL,
  active TINYINT(1) NOT NULL DEFAULT 1,
  description TEXT DEFAULT NULL,
  created_at DATETIME DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
