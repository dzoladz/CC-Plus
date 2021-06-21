#!/bin/bash

/etc/init.d/mysql start \
    && (printf "ERCI\nsupport@example.com\ntest_cons\nY\npassword\n\n" && cat) | php artisan ccplus:addconsortium \
    && echo "done!"
