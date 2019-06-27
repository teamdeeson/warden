#! /bin/bash

ENV="@dev"
if [[ -n "$1" ]]; then
  ENV=$1
fi

php bin/console deeson:warden:build-dashboard --env=${ENV:1}
