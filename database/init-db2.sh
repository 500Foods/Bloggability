#!/bin/bash
db2 -t -x -f createdb-db2.sql | grep -Ev '^(DB20000I|[[:space:]]*$)' | awk '{$1=$1};1'

