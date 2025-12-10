-- Charset/engine padrão
SET NAMES utf8mb4;
SET time_zone = "+00:00";

CREATE TABLE users (
  id            BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  name          VARCHAR(120) NOT NULL,
  email         VARCHAR(160) NOT NULL UNIQUE,
  password_hash VARCHAR(255) NOT NULL,
  role          ENUM('member','partner','affiliate','admin') NOT NULL DEFAULT 'member',
  status        ENUM('active','suspended') NOT NULL DEFAULT 'active',
  created_at    TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at    TIMESTAMP NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE partners (
  id          BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  user_id     BIGINT UNSIGNED NOT NULL,
  business_name VARCHAR(160) NOT NULL,
  cnpj        VARCHAR(32) NULL,
  segment     VARCHAR(80) NULL,
  city        VARCHAR(80) NULL,
  status      ENUM('pending','approved','rejected') NOT NULL DEFAULT 'pending',
  created_at  TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT fk_partners_user FOREIGN KEY (user_id) REFERENCES users(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE plans (
  id          VARCHAR(32) PRIMARY KEY,                 -- 'start' | 'plus' | 'prime'
  name        VARCHAR(80) NOT NULL,
  monthly_price DECIMAL(10,2) NOT NULL,
  yearly_monthly_price DECIMAL(10,2) NULL,             -- preço "equivalente/mês" no anual
  status      ENUM('active','inactive') NOT NULL DEFAULT 'active',
  features    JSON NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO plans (id,name,monthly_price,yearly_monthly_price,status,features) VALUES
('start','Start',19.90,16.90,'active',JSON_ARRAY('Carteirinha digital','Rede de parceiros')),
('plus','Plus',39.90,33.90,'active',JSON_ARRAY('Tudo do Start','Mais cupons','Suporte priorizado')),
('prime','Prime',69.90,59.40,'active',JSON_ARRAY('Tudo do Plus','Benefícios exclusivos','Eventos'));

CREATE TABLE subscriptions (
  id            BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  user_id       BIGINT UNSIGNED NOT NULL,
  plan_id       VARCHAR(32) NOT NULL,
  billing_period ENUM('monthly','annual') NOT NULL DEFAULT 'monthly',
  asaas_customer_id VARCHAR(64) NULL,
  asaas_subscription_id VARCHAR(64) NULL,
  status        ENUM('active','suspended','canceled') NOT NULL DEFAULT 'active',
  started_at    DATE NOT NULL,
  renew_at      DATE NULL,
  value         DECIMAL(10,2) NOT NULL,
  created_at    TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at    TIMESTAMP NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  CONSTRAINT fk_sub_user FOREIGN KEY (user_id) REFERENCES users(id),
  CONSTRAINT fk_sub_plan FOREIGN KEY (plan_id) REFERENCES plans(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE invoices (
  id               BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  subscription_id  BIGINT UNSIGNED NOT NULL,
  asaas_invoice_id VARCHAR(64) NULL,
  value            DECIMAL(10,2) NOT NULL,
  status           ENUM('pending','paid','overdue','canceled') NOT NULL DEFAULT 'pending',
  due_date         DATE NULL,
  paid_at          DATETIME NULL,
  raw              JSON NULL,
  created_at       TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT fk_inv_sub FOREIGN KEY (subscription_id) REFERENCES subscriptions(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE benefits (
  id          BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  partner_id  BIGINT UNSIGNED NULL,
  title       VARCHAR(160) NOT NULL,
  description TEXT NULL,
  category    VARCHAR(40) NOT NULL,
  badge       VARCHAR(24) NULL,
  plans       SET('start','plus','prime') NOT NULL,
  starts_at   DATE NULL,
  ends_at     DATE NULL,
  status      ENUM('active','paused','expired') NOT NULL DEFAULT 'active',
  created_at  TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT fk_benef_partner FOREIGN KEY (partner_id) REFERENCES partners(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE coupons (
  id          BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  code        VARCHAR(40) NOT NULL UNIQUE,
  user_id     BIGINT UNSIGNED NOT NULL,
  benefit_id  BIGINT UNSIGNED NOT NULL,
  status      ENUM('issued','used','revoked') NOT NULL DEFAULT 'issued',
  created_at  TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  used_at     DATETIME NULL,
  CONSTRAINT fk_cpn_user FOREIGN KEY (user_id) REFERENCES users(id),
  CONSTRAINT fk_cpn_benef FOREIGN KEY (benefit_id) REFERENCES benefits(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE affiliate_links (
  id       BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  user_id  BIGINT UNSIGNED NOT NULL,
  code     VARCHAR(24) NOT NULL UNIQUE,   -- ex: USER123
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT fk_aff_user FOREIGN KEY (user_id) REFERENCES users(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE affiliate_clicks (
  id        BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  link_id   BIGINT UNSIGNED NOT NULL,
  ip        VARCHAR(45) NULL,
  ua        VARCHAR(255) NULL,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT fk_click_link FOREIGN KEY (link_id) REFERENCES affiliate_links(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE affiliate_conversions (
  id              BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  link_id         BIGINT UNSIGNED NOT NULL,
  user_id         BIGINT UNSIGNED NULL,        -- novo assinante
  subscription_id BIGINT UNSIGNED NULL,
  amount          DECIMAL(10,2) NOT NULL,
  commission      DECIMAL(10,2) NOT NULL,
  status          ENUM('pending','approved','rejected') NOT NULL DEFAULT 'pending',
  created_at      TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT fk_conv_link FOREIGN KEY (link_id) REFERENCES affiliate_links(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE webhooks_log (
  id         BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  provider   VARCHAR(40) NOT NULL,
  event      VARCHAR(120) NULL,
  signature  VARCHAR(200) NULL,
  payload    JSON NULL,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Índices úteis
CREATE INDEX idx_sub_user ON subscriptions(user_id);
CREATE INDEX idx_inv_sub ON invoices(subscription_id);
CREATE INDEX idx_benef_plans ON benefits(plans);
CREATE INDEX idx_coupon_user ON coupons(user_id);
