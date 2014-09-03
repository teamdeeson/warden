#! /bin/bash

ENV="@dev"
if [[ -n "$1" ]]; then
  ENV=$1
fi

php app/console server:run -e ${ENV:1}
