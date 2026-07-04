-- ########################################################
-- Aegis Filter – MySQL Init Script
-- Crea la base de datos y configura charset
-- ########################################################

CREATE DATABASE IF NOT EXISTS aegisfilter_db
    CHARACTER SET utf8mb4
    COLLATE utf8mb4_unicode_ci;

USE aegisfilter_db;

-- Otorgar permisos al usuario de la aplicación
GRANT ALL PRIVILEGES ON aegisfilter_db.* TO 'aegis_user'@'%';
FLUSH PRIVILEGES;
