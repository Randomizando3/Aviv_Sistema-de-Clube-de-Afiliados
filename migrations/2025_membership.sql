-- 1) Garante plano Free (id = 'Free')
INSERT INTO plans (id, name, price_monthly, price_yearly, status, sort_order)
SELECT 'Free','Free',0,0,'active',-999
WHERE NOT EXISTS (SELECT 1 FROM plans WHERE id='Free');

-- 2) Adiciona coluna de plano corrente no usuário
SET @db := DATABASE();

-- add users.current_plan_id
SET @sql := (
  SELECT IF(
    EXISTS(
      SELECT 1 FROM information_schema.COLUMNS
      WHERE TABLE_SCHEMA=@db AND TABLE_NAME='users' AND COLUMN_NAME='current_plan_id'
    ),
    'SELECT 1',
    'ALTER TABLE users ADD COLUMN current_plan_id VARCHAR(32) NOT NULL DEFAULT ''Free'' AFTER role'
  )
);
PREPARE s1 FROM @sql; EXECUTE s1; DEALLOCATE PREPARE s1;

-- índice
SET @sql := (
  SELECT IF(
    EXISTS(
      SELECT 1 FROM information_schema.STATISTICS
      WHERE TABLE_SCHEMA=@db AND TABLE_NAME='users' AND INDEX_NAME='idx_users_plan'
    ),
    'SELECT 1',
    'CREATE INDEX idx_users_plan ON users (current_plan_id)'
  )
);
PREPARE s2 FROM @sql; EXECUTE s2; DEALLOCATE PREPARE s2;

-- FK (só cria se não existir e se o Free existir)
SET @sql := (
  SELECT IF(
    EXISTS(
      SELECT 1
      FROM information_schema.TABLE_CONSTRAINTS
      WHERE CONSTRAINT_SCHEMA=@db AND TABLE_NAME='users' AND CONSTRAINT_NAME='fk_users_current_plan'
    ),
    'SELECT 1',
    'ALTER TABLE users
       ADD CONSTRAINT fk_users_current_plan
         FOREIGN KEY (current_plan_id) REFERENCES plans(id)
         ON UPDATE CASCADE ON DELETE RESTRICT'
  )
);
PREPARE s3 FROM @sql; EXECUTE s3; DEALLOCATE PREPARE s3;

-- 3) Hardening leve nas tabelas já existentes
-- subscriptions.status: mantém VARCHAR, mas normalizamos valores via código
-- invoices.status: já usa enum('pending','paid','overdue','canceled')
