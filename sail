#!/usr/bin/env bash

# Determine if Docker binary is docker or docker.exe
if command -v docker &> /dev/null; then
    DOCKER_BIN="docker"
elif command -v docker.exe &> /dev/null; then
    DOCKER_BIN="docker.exe"
elif [ -x "/mnt/c/Program Files/Docker/Docker/resources/bin/docker.exe" ]; then
    DOCKER_BIN="/mnt/c/Program Files/Docker/Docker/resources/bin/docker.exe"
else
    DOCKER_BIN="docker"
fi

# Check if vendor/bin/sail exists locally
if [ -f ./vendor/bin/sail ]; then
    exec ./vendor/bin/sail "$@"
else
    if [ "$1" == "composer" ] && ( [ "$2" == "install" ] || [ "$2" == "update" ] ); then
        echo "Running Composer $2 via Sail Docker container..."
        exec $DOCKER_BIN run --rm -v "$(pwd):/var/www/html" -w /var/www/html laravelsail/php83-composer:latest composer "$2" --ignore-platform-reqs
    elif [ $# -gt 0 ]; then
        if [ "$1" == "up" ] || [ "$1" == "down" ] || [ "$1" == "ps" ] || [ "$1" == "build" ] || [ "$1" == "logs" ]; then
            exec $DOCKER_BIN compose "$@"
        else
            exec $DOCKER_BIN compose exec laravel.test php "$@"
        fi
    else
        exec $DOCKER_BIN compose ps
    fi
fi
