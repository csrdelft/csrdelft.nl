#!/bin/bash

set -ev

# Als de commit message [skipbuild] bevat, skip dan deze dingen, deploy wordt wel gedaan.
if (( $SKIP_BUILD == 1 )); then

cd $TRAVIS_BUILD_DIR

# Zet scripts op de juiste plek
cp $TRAVIS_BUILD_DIR/bin/ci/.env.local $TRAVIS_BUILD_DIR/.env.local

# Stel een database in voor composer.
mysql -e 'CREATE DATABASE IF NOT EXISTS csrdelft;'

# Installeer js dependencies
yarn
# Lint js
yarn run lint
# Compileer js
yarn run production

# Installeer php dependencies
composer install
# Verwijder dev dependencies en optimize autoloader
composer install --no-dev --optimize-autoloader

fi;
