#!/bin/bash

# Set environment variables for testing
export APP_ENV=testing
export APP_KEY=base64:2P1YPac4qyp5/M3y/4gB4XIeNNt+gd4b0lK+V1ekXIM=
export DB_CONNECTION=sqlite
export DB_DATABASE=:memory:
export CACHE_STORE=array
export SESSION_DRIVER=array
export QUEUE_CONNECTION=sync

# Run PHPUnit tests
php artisan test "$@"