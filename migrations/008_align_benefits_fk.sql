-- Alinha tipos e recria FKs sem travar em dependências
SET @OLD_FK = @@FOREIGN_KEY_CHECKS;
SET FOREIGN_KEY_CHECKS = 0;

-- Descobre e remove a FK atual de coupons -> benefits (se existir)
SET @fk := (
  SELECT CONSTRAINT_NAME
  FROM information_schema.REFERENTIAL_CONSTRAINTS
  WHERE CONSTRAINT_SCHEMA='aviv'
    AND TABLE_NAME='coupons'
    AND REFERENCED_TABLE_NAME='benefits'
  LIMIT 1
);
SET @sql := IF(@fk IS NULL, 'SELECT 1', CONCAT('ALTER TABLE aviv.coupons DROP FOREIGN KEY ', @fk));
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- Tabelas dependentes e principal
DROP TABLE IF EXISTS aviv.benefit_plans;
DROP TABLE IF EXISTS aviv.benefits;

-- Garanta que coupons.benefit_id seja INT UNSIGNED (para casar com PK abaixo)
ALTER TABLE aviv.coupons
  MODIFY COLUMN benefit_id INT UNSIGNED NOT NULL;

-- Recria benefits com PK UNSIGNED e colunas esperadas
CREATE TABLE aviv.benefits (
  id          INT UNSIGNED NOT NULL AUTO_INCREMENT,
  title       VARCHAR(120) NOT NULL,
  partner     VARCHAR(120) DEFAULT NULL,
  `type`      VARCHAR(50)  NOT NULL DEFAULT 'link',
  category    VARCHAR(50)  NOT NULL DEFAULT 'outros',
  image_url   VARCHAR(255) DEFAULT NULL,
  description TEXT NULL,
  url         VARCHAR(255) DEFAULT NULL,
  discount    VARCHAR(50)  DEFAULT NULL,
  terms       TEXT NULL,
  is_active   TINYINT(1)   NOT NULL DEFAULT 1,
  created_at  DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at  DATETIME     NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  KEY idx_active (is_active)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Recria tabela de vínculo com FK correta
CREATE TABLE aviv.benefit_plans (
  benefit_id INT UNSIGNED NOT NULL,
  plan_id    VARCHAR(50) NOT NULL,
  PRIMARY KEY (benefit_id, plan_id),
  CONSTRAINT fk_bp_benefit
    FOREIGN KEY (benefit_id) REFERENCES aviv.benefits(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Recria a FK de coupons -> benefits com tipos alinhados
ALTER TABLE aviv.coupons
  ADD CONSTRAINT fk_cpn_benef
  FOREIGN KEY (benefit_id) REFERENCES aviv.benefits(id)
  ON DELETE CASCADE;

-- (Opcional) Seed de teste
INSERT INTO aviv.benefits
(title, partner, `type`, category, image_url, description, url, discount, terms, is_active)
VALUES
('Benefício Teste', 'Parceiro X', 'link', 'outros', NULL, 'Descrição teste',
 'https://exemplo.com', '10% OFF', NULL, 1);

SET FOREIGN_KEY_CHECKS = @OLD_FK;
