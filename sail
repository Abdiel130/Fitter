#!/usr/bin/env bash

# Determine if Docker is available
if command -v docker &> /dev/null; then
    DOCKER_BIN="docker"
elif [ -x "/mnt/c/Program Files/Docker/Docker/resources/bin/docker" ]; then
    DOCKER_BIN="/mnt/c/Program Files/Docker/Docker/resources/bin/docker"
else
    DOCKER_BIN="docker"
fi

if [ -f ./vendor/bin/sail ]; then
    exec ./vendor/bin/sail "$@"
else
    if [ $# -gt 0 ]; then
        if [ "$1" == "up" ] || [ "$1" == "down" ] || [ "$1" == "ps" ] || [ "$1" == "build" ] || [ "$1" == "logs" ]; then
            exec $DOCKER_BIN compose "$@"
        else
            exec $DOCKER_BIN compose exec laravel.test php "$@"
        fi
    else
        exec $DOCKER_BIN compose ps
    fi
fi
