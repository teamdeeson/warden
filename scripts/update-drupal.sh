#! /bin/bash

NEWONLY=""
if [[ -n "$1" ]]; then
  NEWONLY='--import-new'
fi

php app/console deeson:site-status:drupal-update ${NEWONLY}
