#!/bin/bash

TEMP_DIR=$(mktemp -d)
echo Using temp dir $TEMP_DIR

echo Calculating total percentage
percent=$(php artisan test --coverage | tee >(cat >&2) | grep Total: | grep -oP '\d+\.\d+ %')
echo Total = $percent
echo "{\"coverage\": \"$percent\"}" > $TEMP_DIR/coverage.json

echo Generating HTML report
php artisan test --coverage-html $TEMP_DIR/

echo Coverage is generated at $TEMP_DIR

# If needed, store the coverage info somewhere
#
# chmod 755 $TEMP_DIR # mktemp creates with 700 by default, we need to relax it to be readable by web server
# rsync -e 'ssh -p 2222' -rlpvz --delete $TEMP_DIR/ user@host:/usr/share/nginx/html/coverage/
# scp -P 2222 $TEMP_DIR/coverage.json user@host:/usr/share/nginx/html/public/coverage.json
