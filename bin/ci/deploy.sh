#!/usr/bin/env bash

# Exit with nonzero exit code if anything fails
set -e

# Get the deploy key
cd $TRAVIS_BUILD_DIR/bin/ci
ENCRYPTED_KEY_VAR="encrypted_190bb3706463_key"
ENCRYPTED_IV_VAR="encrypted_190bb3706463_iv"
ENCRYPTED_KEY=${!ENCRYPTED_KEY_VAR}
ENCRYPTED_IV=${!ENCRYPTED_IV_VAR}
openssl aes-256-cbc -K $ENCRYPTED_KEY -iv $ENCRYPTED_IV -in deploy_key.enc -out deploy_key -d
chmod 600 deploy_key
eval $(ssh-agent -s)
ssh-add deploy_key
rm deploy_key

cd $TRAVIS_BUILD_DIR
# Deploy for web app
git config --global user.email "travis@travis-ci.org"
git config --global user.name "Travis CI"

cd /home/travis/build

git clone --quiet --branch=master git@github.com:csrdelft/productie.git deploy > /dev/null
rm -rf deploy/*
rsync -a csrdelft/csrdelft.nl/ deploy/ --exclude node_modules --exclude .git --exclude resources --exclude vendor
mv deploy/bin/ci/.gitignore.prod deploy/.gitignore

cd deploy
git add -A
git diff-index --quiet HEAD || git commit -m "Travis deploy $TRAVIS_BUILD_NUMBER"
git push --force --quiet --set-upstream origin master
