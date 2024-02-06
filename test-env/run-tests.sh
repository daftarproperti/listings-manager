#!/bin/bash
# Entry point of mls-sys feature tests.

composer install

./test-env/wait-mongodb.sh || { echo "MongoDB did not become available, exiting..."; exit 1; }

# mongodb is ready, do the test
php artisan test
