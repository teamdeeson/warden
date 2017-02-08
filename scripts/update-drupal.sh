#!/bin/bash

NEWONLY=""
ENV="$1"

if [[ -z "${ENV}" ]]; then
  echo "Error: No environment variable provided! First argument must be @dev, @test or @prod."
  usage
  exit 1;
fi

if [[ -n "$2" ]]; then
  NEWONLY='--import-new'
fi

php app/console deeson:warden:drupal-update --env=${ENV:1} ${NEWONLY}
