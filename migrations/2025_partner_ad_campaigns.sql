-- ==============================================
-- Campanhas de anúncios + vínculo no pedido
-- ==============================================
SET @db := DATABASE();

-- 1) Tabela de campanhas do parceiro
CREATE TABLE IF NOT EXISTS partner_ad_campaigns (
  id         BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
  partner_id BIGINT UNSIGNED NOT NULL,
  user_id    BIGINT UNSIGNED NOT NULL,
  title      VARCHAR(160) NOT NULL,
  target_url VARCHAR(255) NULL,

  -- imagens
  img_sky_1     VARCHAR(255) NULL, -- arranha-céu (lateral) 1
  img_sky_2     VARCHAR(255) NULL, -- arranha-céu (lateral) 2
  img_top_468   VARCHAR(255) NULL, -- topo 468 de largura
  img_square_1  VARCHAR(255) NULL, -- quadrado 1
  img_square_2  VARCHAR(255) NULL, -- quadrado 2

  status     ENUM('inactive','active','paused') NOT NULL DEFAULT 'inactive',
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP NULL ON UPDATE CURRENT_TIMESTAMP,

  KEY idx_camp_partner (partner_id),
  KEY idx_camp_user    (user_id),
  CONSTRAINT fk_camp_partner FOREIGN KEY (partner_id) REFERENCES partners(id),
  CONSTRAINT fk_camp_user    FOREIGN KEY (user_id)    REFERENCES users(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 2) Adiciona campaign_id no pedido (se não existir)
SET @need_col := (
  SELECT COUNT(*)=0 FROM INFORMATION_SCHEMA.COLUMNS
  WHERE TABLE_SCHEMA=@db AND TABLE_NAME='partner_ad_orders' AND COLUMN_NAME='campaign_id'
);

SET @sql := IF(@need_col,
  'ALTER TABLE partner_ad_orders
     ADD COLUMN campaign_id BIGINT UNSIGNED NULL AFTER plan_id,
     ADD KEY idx_adorders_campaign (campaign_id),
     ADD CONSTRAINT fk_adorders_campaign FOREIGN KEY (campaign_id) REFERENCES partner_ad_campaigns(id);',
  'SELECT 1'
);
PREPARE s1 FROM @sql; EXECUTE s1; DEALLOCATE PREPARE s1;
