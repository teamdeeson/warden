#! /bin/bash

NEWONLY=""
if [[ -n "$1" ]]; then
  NEWONLY='--import-new'
fi

php bin/console deeson:warden:update-sites ${NEWONLY}
