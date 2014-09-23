#! /bin/bash

NEWONLY=""
if [[ -n "$1" ]]; then
  NEWONLY='--import-new'
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
fi