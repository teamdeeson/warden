#! /bin/bash

NEWONLY=""
if [[ -n "$1" ]]; then
  # new flag is passed in but if the time is 04:00 then run full import instead.
  TIME=$(date "+%H%M")
  echo $TIME
  if [[ $TIME -gt 400 ]] && [[ $TIME -lt 405 ]]; then
    echo "Run full update"
  else
    echo "Run new only import"
    NEWONLY='--import-new'
  fi
fi

DIR="$( cd "$( dirname "${BASH_SOURCE[0]}" )" && pwd )"
OUTPUT=""

OUTPUT=$("${DIR}/update-sites.sh" ${NEWONLY})
echo "$OUTPUT"
SIZE=${#OUTPUT}
#echo "size: ${SIZE}"

if [[ "$SIZE" -gt 0 ]]; then
  "${DIR}/update-modules.sh" ${NEWONLY}
  "${DIR}/update-drupal.sh" ${NEWONLY}
  "${DIR}/build-sites-have-issues.sh"
else
  echo "Nothing to be updated"
fi