#!/bin/bash
# Ushahidi cron script
if [ $# -lt 2 ] ; then
  echo "Usage: ushahidi_cron [docroot] [baseurl]"
  exit 1
fi

DOCROOT=$1
BASEURL=$2
LOCK_ID=`echo $BASEURL | sed -r -e "s#http[s]{0,1}://##g"`

(
flock -x -w 5 200

CURL=`command -v curl`
if [ $? -eq 0 ]; then
  GET_CMD="$CURL -s"
else
  WGET=`command -v wget`
  if [ $? -eq 0 ]; then
    GET_CMD="$WGET -qO-"
  else
    echo "Couldn't find wget or curl"
    exit 1
  fi 
fi

LOG_FILE="$DOCROOT/application/logs/cron.log"

touch $LOG_FILE
date >> $LOG_FILE && $GET_CMD "$BASEURL/scheduler?debug=1" | /bin/sed 's/<BR \/>/\n/g' >> $LOG_FILE
) 200>/tmp/ushahidi_cron_lock_$LOCK_ID
