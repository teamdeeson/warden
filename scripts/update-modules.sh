#! /bin/bash

NEWONLY=""
if [[ -n "$1" ]]; then
  NEWONLY='--import-new'
fi

php app/console deeson:warden:update-modules ${NEWONLY}
