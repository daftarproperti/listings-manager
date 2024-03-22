#!/bin/bash

TEMP_DIR=$(mktemp -d)
echo Using temp dir $TEMP_DIR

echo Calculating total percentage
percent=$(php artisan test --coverage | grep Total: | grep -oP '\d+\.\d+ %')
echo Total = $percent
echo "{\"coverage\": \"$percent\"}" > $TEMP_DIR/coverage.json

echo Generating HTML report
php artisan test --coverage-html $TEMP_DIR/

echo Copying to artifacts.jlrm.net
chmod 755 $TEMP_DIR # mktemp creates with 700 by default, we need to relax it to be readable by web server
rsync -e 'ssh -p 2222' -rlpvz --delete $TEMP_DIR/ root@artifacts.jlrm.net:/usr/share/nginx/html/coverage/
scp -P 2222 $TEMP_DIR/coverage.json root@artifacts.jlrm.net:/usr/share/nginx/html/public/coverage.json
