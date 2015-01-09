#!/bin/bash

# get current directory
DIR="$( cd "$( dirname "$0" )" && pwd )"

# assure our logfile belongs to user postfix
touch /var/log/ratelimit-policyd.log
chown postfix:postfix /var/log/ratelimit-policyd.log

# install init script
ln -sf "$DIR/init.d/ratelimit-policyd" /etc/init.d/
insserv ratelimit-policyd
