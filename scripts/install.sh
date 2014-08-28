#! /bin/bash

php /usr/local/bin/composer update

rm -rf app/cache/*
rm -rf app/logs/*

sudo chown apache:deedev app/cache
sudo chown apache:deedev app/logs