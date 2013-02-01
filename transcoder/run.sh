#!/bin/bash
MAXWORKERS=10

BASEDIR=$(dirname $0)
DATETIME=$(date +%Y-%m-%d-%T)

RUNNING=`ps aux | grep run.sh | grep -v grep | wc -l`
if [ "$RUNNING" -gt "$MAXWORKERS" ]
then    
    echo "$(date +%Y-%m-%d-%T) - Another spawn script is already running"
else
    while true
    do
    	
        PHPRUNNING=`ps aux | grep run.php | grep -v grep | wc -l`
        if [ "$PHPRUNNING" -lt "$MAXWORKERS" ]
        then
            echo "$(date +%Y-%m-%d-%T) - Spawning Worker $PHPRUNNING"
            php $BASEDIR/run.php &
        fi
        
        sleep 5
   done
fi 