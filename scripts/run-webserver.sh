#! /bin/bash

ENV="@dev"
if [[ -n "$1" ]]; then
  ENV=$1
fi

php app/console server:run localhost:8010 -e ${ENV:1}

#php -c /etc/php.ini -S localhost:8000 -t web web/app_local.php