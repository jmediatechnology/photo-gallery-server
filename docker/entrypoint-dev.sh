#!/bin/bash
set -e

composer install --no-interaction

exec "$@"
