#!/bin/bash
if [ "$1" == "local" ]; then
    cp .env.local .env
    echo "Switched to LOCAL environment"
elif [ "$1" == "production" ]; then
    cp .env.production .env
    echo "Switched to PRODUCTION environment"
else
    echo "Usage: ./switch-env.sh [local|production]"
fi

php artisan config:clear
php artisan cache:clear