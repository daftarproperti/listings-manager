#!/bin/bash
# Entry point of daftarproperti feature tests.

composer install

# To make sure vite manifest is created
npm install
npm run build || { echo "Failed npm run build"; exit 1; }
npm run lint || { echo "Failed lint"; exit 1; }
npm run knip || { echo "Failed knip"; exit 1; }

./test-env/wait-mongodb.sh || { echo "MongoDB did not become available, exiting..."; exit 1; }

# mongodb is ready, do the test
php artisan test
