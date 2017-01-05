# Instalation

bash install.sh

apt-get update
apt-get install composer php-mbstring mysql-server php-mysql
composer install

mysql --execute="CREATE DATABASE IF NOT EXISTS training;"

nano /etc/mysql/mysql.conf.d/mysqld.cnf 
bind address
