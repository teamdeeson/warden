#! /bin/bash

env="@dev"
options=""
if [[ -n "$1" ]]; then
  env=$1
  if [[ $1 == "@dev" ]]; then
    options="--relative"
  fi
fi

runaswebserver="No"
if [[ -n "$2" ]]; then
  runaswebserver="Yes"
fi

if [[ "${runaswebserver}" == "Yes" ]]; then
  # To run as web server user.
  sudo su - www-data -s /bin/bash -c "cd $PWD; php app/console --env=${env:1} cache:clear --no-debug; php app/console --env=${env:1} assets:install web"
else
  php bin/console --env=${env:1} cache:clear --no-debug
  php bin/console --env=${env:1} assets:install ${options} web
fi
