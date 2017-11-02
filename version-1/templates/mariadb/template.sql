CREATE DATABASE IF NOT EXISTS {DBNAME} CHARACTER SET utf8 COLLATE utf8_general_ci;
CREATE USER '{USERNAME}'@'localhost' IDENTIFIED BY '{PASSWORD}';
GRANT ALL PRIVILEGES ON {DBNAME}.* TO '{USERNAME}'@'localhost';
FLUSH PRIVILEGES;