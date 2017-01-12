#!/bin/bash

export LC_ALL=C # all commands in english http://askubuntu.com/questions/264283/switch-command-output-language-from-native-language-to-english

sudo apt-get install mysql-server composer php-mbstring php-mysql

#OLDPWD=`pwd`;

#sudo curl https://gist.githubusercontent.com/gustawdaniel/79aae802d0c99ba3ef633efa441d5863/raw/3a0cd4a9bc0ba819b5e87db166013ae2b0bc8b84/php7.conf%2520 -o /usr/src/php7.conf 
#sudo curl https://gist.githubusercontent.com/gustawdaniel/79aae802d0c99ba3ef633efa441d5863/raw/3a0cd4a9bc0ba819b5e87db166013ae2b0bc8b84/php_install.sh -o /usr/src/php_install.sh
#cd /usr/src && sudo bash php_install.sh

#cd $OLDPWD; pwd;
#curl -sS https://getcomposer.org/installer | php
#sudo mv composer.phar /usr/local/bin/composer



#curl https://gist.githubusercontent.com/pkuczynski/8665367/raw/8dd42e311ddf98ec79e1ab1bf5bf298475113174/parse_yaml.sh \
#    > bash/lib/parse_yaml.sh

composer install;

. bash/lib/parse_yaml.sh
eval $(parse_yaml config/parameters.yml "config_")

mysql -u $config_parameters_user -e "CREATE DATABASE IF NOT EXISTS $config_parameters_dbname";
mysql -u $config_parameters_user $config_parameters_dbname < sql/do_test_procedure.sql;
php purge.php

echo "Next step: bash bash/initialize.sh";

