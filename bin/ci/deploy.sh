#!/usr/bin/env bash

# Exit with nonzero exit code if anything fails
set -e

# Get the deploy key
cd /home/travis/build/csrdelft/csrdelft.nl/bin/ci
ENCRYPTED_KEY_VAR="encrypted_190bb3706463_key"
ENCRYPTED_IV_VAR="encrypted_190bb3706463_iv"
ENCRYPTED_KEY=${!ENCRYPTED_KEY_VAR}
ENCRYPTED_IV=${!ENCRYPTED_IV_VAR}
openssl aes-256-cbc -K $ENCRYPTED_KEY -iv $ENCRYPTED_IV -in deploy_key.enc -out deploy_key -d
chmod 600 deploy_key
eval `ssh-agent -s`
ssh-add deploy_key

# Deploy for web app
cd /home/travis/build
git config --global user.email "travis@travis-ci.org"
git config --global user.name "Travis CI"
git clone --quiet --branch=dist git@github.com:csrdelft/csrdelft.nl.git deploy > /dev/null
rm -rf ./deploy/*
cp -Rf /home/travis/build/csrdelft/csrdelft.nl/* ./deploy/
cd deploy
git add --all .
git add -f htdocs/dist
git commit -m "Travis deploy $TRAVIS_BUILD_NUMBER"
git push --force --quiet git@github.com:csrdelft/csrdelft.git dist > /dev/null