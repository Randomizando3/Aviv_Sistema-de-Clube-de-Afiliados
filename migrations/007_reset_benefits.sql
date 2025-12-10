-- Guarda o valor atual e desabilita checagem de FKs
SET @OLD_FOREIGN_KEY_CHECKS = @@FOREIGN_KEY_CHECKS;
SET FOREIGN_KEY_CHECKS = 0;

-- Zera tabelas relacionadas
DROP TABLE IF EXISTS aviv.benefit_plans;
DROP TABLE IF EXISTS aviv.benefits;

-- Recria schema canônico
CREATE TABLE aviv.benefits (
  id          INT NOT NULL AUTO_INCREMENT,
  title       VARCHAR(120) NOT NULL,
  partner     VARCHAR(120) DEFAULT NULL,
  type        VARCHAR(50)  NOT NULL DEFAULT 'link',
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

CREATE TABLE aviv.benefit_plans (
  benefit_id INT NOT NULL,
  plan_id    VARCHAR(50) NOT NULL,
  PRIMARY KEY (benefit_id, plan_id),
  CONSTRAINT fk_bp_benefit
    FOREIGN KEY (benefit_id) REFERENCES aviv.benefits(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Seed de teste
INSERT INTO aviv.benefits
(title, partner, type, category, image_url, description, url, discount, terms, is_active)
VALUES
('Benefício Teste', 'Parceiro X', 'link', 'outros', NULL, 'Descrição teste',
 'https://exemplo.com', '10% OFF', NULL, 1);

-- Restaura a checagem de FKs
SET FOREIGN_KEY_CHECKS = @OLD_FOREIGN_KEY_CHECKS;
