#!/bin/bash

if [[ -z "${1}" ]]; then
  echo "Error: No environment variable provided! First argument must be @dev, @test or @prod."
  exit 1;
fi

NEWONLY=""
if [[ -n "$2" ]]; then
  NEWONLY='--import-new'
fi

php app/console deeson:warden:drupal-update --env=${ENV:1} ${NEWONLY}
