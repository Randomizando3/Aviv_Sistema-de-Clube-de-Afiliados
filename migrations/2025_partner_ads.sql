-- 2.1) benefits: quem enviou + moderação
ALTER TABLE benefits
  ADD COLUMN submitted_by BIGINT UNSIGNED NULL AFTER url,
  ADD COLUMN moderation_status ENUM('pending','approved','rejected') NOT NULL DEFAULT 'pending' AFTER active,
  ADD KEY idx_benef_submitted_by (submitted_by),
  ADD CONSTRAINT fk_benef_submitted_by FOREIGN KEY (submitted_by) REFERENCES users(id);

-- 2.2) catálogos de planos de publicidade
CREATE TABLE IF NOT EXISTS ad_plans (
  id            BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
  name          VARCHAR(60)  NOT NULL,           -- Inicial, Intermediário, Expert, Ultimate
  description   TEXT NULL,
  view_quota    INT NOT NULL,                    -- 500, 1000, 5000, 20000
  price         DECIMAL(10,2) NOT NULL DEFAULT 0.00,
  status        ENUM('active','inactive') NOT NULL DEFAULT 'active',
  sort_order    INT NOT NULL DEFAULT 0,
  created_at    TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 2.3) pedidos de publicidade do parceiro
CREATE TABLE IF NOT EXISTS partner_ad_orders (
  id              BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
  partner_id      BIGINT UNSIGNED NOT NULL,  -- FK -> partners.id (quem é o parceiro “PJ”)
  user_id         BIGINT UNSIGNED NOT NULL,  -- FK -> users.id (dono da conta)
  plan_id         BIGINT UNSIGNED NOT NULL,  -- FK -> ad_plans.id
  title           VARCHAR(160) NOT NULL,     -- rótulo interno do pedido
  banner_image    VARCHAR(255) NULL,         -- URL do banner (upload)
  target_url      VARCHAR(255) NULL,         -- clique
  placements      JSON NULL,                 -- ex.: ["member","site","specialty_rank"]
  specialties     JSON NULL,                 -- lista de especialidades alvo (para ranking)
  start_at        DATETIME NULL,
  end_at          DATETIME NULL,
  status          ENUM('draft','pending_payment','active','paused','exhausted','canceled') NOT NULL DEFAULT 'pending_payment',
  quota_total     INT NOT NULL,              -- copia de ad_plans.view_quota no momento da compra
  quota_used      INT NOT NULL DEFAULT 0,
  asaas_invoice_id VARCHAR(64) NULL,
  amount          DECIMAL(10,2) NOT NULL DEFAULT 0.00,  -- valor cobrado
  paid_at         DATETIME NULL,
  created_at      TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at      TIMESTAMP NULL ON UPDATE CURRENT_TIMESTAMP,

  KEY idx_adorders_partner (partner_id),
  KEY idx_adorders_user (user_id),
  KEY idx_adorders_plan (plan_id),
  CONSTRAINT fk_adorders_partner FOREIGN KEY (partner_id) REFERENCES partners(id),
  CONSTRAINT fk_adorders_user    FOREIGN KEY (user_id)    REFERENCES users(id),
  CONSTRAINT fk_adorders_plan    FOREIGN KEY (plan_id)    REFERENCES ad_plans(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 2.4) impressões (tracking)
CREATE TABLE IF NOT EXISTS partner_ad_impressions (
  id           BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
  ad_order_id  BIGINT UNSIGNED NOT NULL,
  ip           VARCHAR(45) NULL,
  ua           VARCHAR(255) NULL,
  referer      VARCHAR(255) NULL,
  created_at   TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  KEY idx_impr_ad (ad_order_id, created_at),
  CONSTRAINT fk_impr_order FOREIGN KEY (ad_order_id) REFERENCES partner_ad_orders(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 2.5) seed inicial para os 4 planos (ajuste preço depois no Admin)
INSERT INTO ad_plans (name, description, view_quota, price, sort_order) VALUES
('Inicial',        'Pacote com 500 visualizações de banner.',    500,   0.00,  1),
('Intermediário',  'Pacote com 1000 visualizações de banner.',   1000,  0.00,  2),
('Expert',         'Pacote com 5000 visualizações de banner.',   5000,  0.00,  3),
('Ultimate',       'Pacote com 20000 visualizações de banner.',  20000, 0.00,  4);
