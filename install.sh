#!/bin/bash

#sudo apt-get install mysql-server

OLDPWD=`pwd`;

#sudo curl https://gist.githubusercontent.com/gustawdaniel/79aae802d0c99ba3ef633efa441d5863/raw/3a0cd4a9bc0ba819b5e87db166013ae2b0bc8b84/php7.conf%2520 -o /usr/src/php7.conf 
#sudo curl https://gist.githubusercontent.com/gustawdaniel/79aae802d0c99ba3ef633efa441d5863/raw/3a0cd4a9bc0ba819b5e87db166013ae2b0bc8b84/php_install.sh -o /usr/src/php_install.sh
#cd /usr/src && sudo bash php_install.sh

cd $OLDPWD; pwd;
curl -sS https://getcomposer.org/installer | php
sudo mv composer.phar /usr/local/bin/composer
composer install;
mysql -u root -e 'CREATE DATABASE IF NOT EXISTS training'
