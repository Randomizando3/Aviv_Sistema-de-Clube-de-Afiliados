CREATE TABLE IF NOT EXISTS affiliate_payouts (
  id                BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  affiliate_user_id BIGINT UNSIGNED NOT NULL,
  amount            DECIMAL(10,2)   NOT NULL,
  status            ENUM('requested','approved','paid','rejected') NOT NULL DEFAULT 'requested',
  pix_type          ENUM('cpf','cnpj','email','phone','evp') NULL,
  pix_key           VARCHAR(160) NULL,
  notes             VARCHAR(255) NULL,
  created_at        TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  processed_at      DATETIME NULL,
  PRIMARY KEY (id),
  KEY idx_payout_affiliate (affiliate_user_id),
  CONSTRAINT fk_payout_user FOREIGN KEY (affiliate_user_id) REFERENCES users (id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
