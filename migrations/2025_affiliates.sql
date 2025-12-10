-- ⚙️ Configs simples (chave/valor)
CREATE TABLE IF NOT EXISTS settings (
  k VARCHAR(64) PRIMARY KEY,
  v VARCHAR(255) NOT NULL
);

-- 👤 Campos no usuário: código de afiliado e quem indicou
ALTER TABLE users
  ADD COLUMN IF NOT EXISTS affiliate_code VARCHAR(32) UNIQUE NULL,
  ADD COLUMN IF NOT EXISTS referred_by INT NULL;

-- 🔗 Registro de indicações (1:1 por indicado)
CREATE TABLE IF NOT EXISTS affiliate_refs (
  id INT AUTO_INCREMENT PRIMARY KEY,
  referrer_id INT NOT NULL,
  referred_user_id INT NOT NULL UNIQUE,
  source VARCHAR(64) NULL,
  created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
  INDEX(referrer_id), INDEX(referred_user_id)
);

-- 💸 Comissões
CREATE TABLE IF NOT EXISTS affiliate_commissions (
  id INT AUTO_INCREMENT PRIMARY KEY,
  affiliate_id INT NOT NULL,         -- quem recebe
  member_id INT NOT NULL,            -- quem pagou (indicado)
  subscription_id INT NULL,
  invoice_id VARCHAR(64) NULL,
  percentage DECIMAL(5,2) NOT NULL,
  amount_gross DECIMAL(10,2) NOT NULL,
  amount_commission DECIMAL(10,2) NOT NULL,
  status ENUM('pending','approved','paid','cancelled') DEFAULT 'approved',
  event VARCHAR(32) DEFAULT 'first_payment', -- simples: só 1a. assinatura (config)
  created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
  paid_at DATETIME NULL,
  note VARCHAR(255) NULL,
  UNIQUE KEY uniq_member_event (member_id, event)
);

-- 🔧 Defaults
INSERT INTO settings (k,v) VALUES
('affiliate.percent','10'),          -- % global
('affiliate.cookie_days','30'),      -- dias do cookie
('affiliate.first_only','1'),        -- 1 = paga só no 1º pagamento
('affiliate.min_payout','50')        -- mínimo para saque (admin controla pagamento manual)
ON DUPLICATE KEY UPDATE v=VALUES(v);
