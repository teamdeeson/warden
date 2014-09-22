#! /bin/bash

ENV="@dev"
if [[ -n "$1" ]]; then
  ENV=$1
fi

DIR="$( cd "$( dirname "${BASH_SOURCE[0]}" )" && pwd )"

git pull
"${DIR}/clear-cache.sh" ${ENV}