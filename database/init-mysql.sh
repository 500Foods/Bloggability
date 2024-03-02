#!/bin/bash
mysql -sN -u bloggable -p blog < createdb-mysql.sql | grep -Ev '^(message|[[:space:]]*$)' | awk '{$1=$1};1'

