#!/bin/bash

set -ev

# Als de commit message [skipbuild] bevat, skip dan deze dingen, deploy wordt wel gedaan.
if (( $SKIP_BUILD == 1 )); then

cd $TRAVIS_BUILD_DIR

# Zet scripts op de juiste plek
cp $TRAVIS_BUILD_DIR/bin/ci/mysql.ini.travis $TRAVIS_BUILD_DIR/etc/mysql.ini
cp $TRAVIS_BUILD_DIR/bin/ci/defines.include.php $TRAVIS_BUILD_DIR/lib/defines.include.php

# Stel een database in voor composer.
mysql -e 'CREATE DATABASE IF NOT EXISTS csrdelft;'

# Installeer js dependencies
yarn
# Compileer js
yarn run production

# Installeer php dependencies
composer install
# Compileer blade
composer run-script production
# Verwijder dev dependencies en optimize autoloader
composer install --no-dev --optimize-autoloader

fi;
