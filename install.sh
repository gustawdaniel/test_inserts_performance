#!/bin/bash

mysql -u root -e "CREATE DATABASE IF NOT EXISTS training";
composer install;
