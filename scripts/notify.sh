#!/bin/bash

function usage
{
    echo "usage: ../scripts/notify.sh [ENVIRONMENT] [TYPE]"
    echo "  ENVIRONMENT  Environment to notify (dev, test or prod)"
    echo "  TYPE         Type of notification to send (slack or email)"
    exit 1
}

if [ "$#" -ne 2 ]
  then
    echo "Error: Command requires 2 arguments."
    usage
fi
if [ -z "$1" ]
  then
      echo "Error: No environment variable provided! First argument must be @dev, @test or @prod."
      usage
fi
if [ -z "$2" ]
  then
      echo "Error: Notification type not provided. Second argument must be 'slack' or 'email."
      usage
fi

if [[ $1 == "test" || $1 == "dev" || $1 == "prod" ]] ; then
    if [[ $2 == "slack" || $2 == "email" ]] ; then
        php app/console deeson:warden:dashboard-send-notification --env=${1} --type=${2}
    else
      echo "Error: Notification type not provided. Second argument must be 'slack' or 'email."
      usage
    fi
else
  echo "Error: No environment variable provided! First argument must be @dev, @test or @prod."
  usage
fi

