#! /bin/bash

ENV="@dev"
if [[ -n "$1" ]]; then
  ENV=$1
fi

RUNASAPACHE="No"
if [[ -n "$2" ]]; then
  RUNASAPACHE="Yes"
fi

if [[ "${RUNASAPACHE}" == "Yes" ]]; then
  # To run as apache user.
  sudo su - apache -c "php $PWD/app/console --env=${ENV:1} cache:clear --no-debug"
  sudo su - apache -c "php $PWD/app/console --env=${ENV:1} assets:install web"
else
  php app/console --env=${ENV:1} cache:clear --no-debug
  php app/console --env=${ENV:1} assets:install web
fi