#!/bin/bash
set -e

php bin/console cache:warmup

exec "$@"
