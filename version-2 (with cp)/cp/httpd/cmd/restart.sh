#!/bin/bash

nPid=`cat /opt/vibus/cp/httpd/pid/httpd.pid`
kill -TERM $nPid
while ps -p $nPid; do sleep 1;done;
httpd -k start -f /opt/vibus/cp/httpd/httpd.conf