-- Create table for Payment Settings
CREATE TABLE IF NOT EXISTS `tb_pengaturan_pembayaran` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `provider` varchar(50) NOT NULL DEFAULT 'midtrans',
  `is_active` tinyint(1) DEFAULT 0,
  `is_production` tinyint(1) DEFAULT 0,
  `server_key` varchar(255) DEFAULT NULL,
  `client_key` varchar(255) DEFAULT NULL,
  `merchant_id` varchar(255) DEFAULT NULL,
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Insert default Midtrans row if doesn't exist
INSERT INTO `tb_pengaturan_pembayaran` (`provider`, `is_active`, `is_production`, `server_key`, `client_key`, `merchant_id`) 
SELECT * FROM (SELECT 'midtrans' AS provider, 1 AS is_active, 1 AS is_prod, 'YOUR_SERVER_KEY_HERE' AS sk, 'YOUR_CLIENT_KEY_HERE' AS ck, 'YOUR_MERCHANT_ID_HERE' AS mid) AS tmp
WHERE NOT EXISTS (
    SELECT provider FROM tb_pengaturan_pembayaran WHERE provider = 'midtrans'
) LIMIT 1;
