#!/bin/bash

composer install;

curl https://gist.githubusercontent.com/pkuczynski/8665367/raw/8dd42e311ddf98ec79e1ab1bf5bf298475113174/parse_yaml.sh \
    > bash/lib/parse_yaml.sh

. bash/lib/parse_yaml.sh
eval $(parse_yaml config/parameters.yml "config_")

mysql -u root -e "CREATE DATABASE IF NOT EXISTS $config_parameters_dbname";

echo "Next step: bash bash/initialize.sh";