#! /bin/bash

function usage
{
    echo "usage: ../scripts/run-updates.sh [--new-only] | [-h]"
    echo "--new-only (optional) use to ONLY update sites that have recently been registered."
}

NEWONLY=""

while [ "$1" != "" ]; do
    case $1 in
        --new-only )   NEWONLY="--import-new"
                       ;;
        -h | --help )  usage
                       exit
                       ;;
    esac
    shift
done


if [[ -n "$NEWONLY" ]]; then
  echo "Only import newly registered sites....."
else
  echo "Run full update....."
fi

DIR="$( cd "$( dirname "${BASH_SOURCE[0]}" )" && pwd )"
OUTPUT=""

OUTPUT=$("${DIR}/update-sites.sh" ${NEWONLY})
echo "$OUTPUT"
SIZE=${#OUTPUT}

if [[ "$SIZE" -gt 0 ]]; then
  "${DIR}/update-drupal.sh" ${NEWONLY}
  "${DIR}/update-modules.sh" ${NEWONLY}
  "${DIR}/build-sites-have-issues.sh"
else
  echo "Nothing to be updated"
fi
