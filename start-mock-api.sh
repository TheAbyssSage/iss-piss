#!/bin/bash
# Standalone Mock API Server
# This runs on a different port to avoid single-threaded deadlock

cd "$(dirname "$0")"
php -S localhost:9000 -t public mock-api.php
