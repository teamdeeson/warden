#! /bin/bash

ENV="@dev"
if [[ -n "$1" ]]; then
  ENV=$1
fi

php app/console cache:clear --env=${ENV:1} --no-debug

# To run as apache user.
#sudo su - apache -c "php $PWD/app/console cache:clear --env=${ENV:1} --no-debug"