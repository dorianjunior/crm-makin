-- ==============================================================================
-- Database Initialization Script
-- ==============================================================================
-- This script runs when the MariaDB container is first created
-- It sets up the initial database and user permissions
-- ==============================================================================

-- Create database with proper charset
CREATE DATABASE IF NOT EXISTS `crm_makin`
    CHARACTER SET utf8mb4
    COLLATE utf8mb4_unicode_ci;

-- Create application user with specific privileges
CREATE USER IF NOT EXISTS 'crm_user'@'%' IDENTIFIED BY 'crm_password_change_me';

-- Grant privileges to application user
GRANT SELECT, INSERT, UPDATE, DELETE, CREATE, DROP, INDEX, ALTER,
      CREATE TEMPORARY TABLES, LOCK TABLES, EXECUTE, CREATE VIEW,
      SHOW VIEW, CREATE ROUTINE, ALTER ROUTINE, TRIGGER, REFERENCES
ON `crm_makin`.* TO 'crm_user'@'%';

-- Create test database for automated testing
CREATE DATABASE IF NOT EXISTS `crm_makin_test`
    CHARACTER SET utf8mb4
    COLLATE utf8mb4_unicode_ci;

-- Grant test database privileges
GRANT ALL PRIVILEGES ON `crm_makin_test`.* TO 'crm_user'@'%';

-- Flush privileges to ensure changes take effect
FLUSH PRIVILEGES;

-- Display success message
SELECT 'Database initialization completed successfully' AS Status;
