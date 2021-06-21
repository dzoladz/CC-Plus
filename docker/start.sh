#!/bin/bash

service mysql start \
    && /usr/sbin/apachectl -D FOREGROUND
