#!/bin/bash

/etc/init.d/mysql start \
    && mysql -e "CREATE USER 'db_user'@'localhost' IDENTIFIED BY 'db_password';" \
    && mysql -e "CREATE USER 'db_user_2'@'localhost' IDENTIFIED BY 'db_password_2';" \
    && mysql -e "UPDATE mysql.user SET Grant_priv='Y', Super_priv='Y' WHERE User='db_user';" \
    && mysql -e "UPDATE mysql.user SET Grant_priv='Y', Super_priv='Y' WHERE User='db_user_2';" \
    && mysql -e "GRANT ALL ON *.* TO 'db_user'@'localhost';" \
    && mysql -e "GRANT ALL ON *.* TO 'db_user_2'@'localhost';" \
    && mysql -e "FLUSH PRIVILEGES;" \
    && mysql -u db_user -pdb_password -e "CREATE DATABASE ccplus_global;" \
    && mysql -u db_user_2 -pdb_password_2 -e "CREATE DATABASE ccplus_con_template;"
