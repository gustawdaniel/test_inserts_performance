insert_test
===========

[![Build Status](https://travis-ci.org/gustawdaniel/test_inserts_performance.svg?branch=master)](https://travis-ci.org/gustawdaniel/test_inserts_performance)

[![Coverage Status](https://coveralls.io/repos/github/gustawdaniel/test_inserts_performance/badge.svg?branch=master)](https://coveralls.io/github/gustawdaniel/test_inserts_performance?branch=master)

Project aiming do tests on database.


Installation

    apt-get install composer php-curl php-simplexml php-mysql mysql-server
    apt-get install composer
    php bin/console doctrine:database:create
    php bin/console app:schema:update --force 1
    php bin/console app:test first 63 50 50