#!/bin/bash

# Quick start script for Laravel API project

if [ "$1" = "docker" ]; then
    echo "Starting with Docker Compose..."
    docker compose up -d
    echo "Waiting for services to start..."
    sleep 5
    echo "Running migrations..."
    docker compose exec app php artisan migrate --force
    echo "✓ API running on http://localhost:10000/api/v1/"
elif [ "$1" = "local" ]; then
    echo "Starting Laravel development server..."
    php artisan serve --host=0.0.0.0 --port=8000
else
    echo "Usage: $0 [docker|local]"
    echo "  docker  - Start with Docker Compose"
    echo "  local   - Start local development server"
fi
