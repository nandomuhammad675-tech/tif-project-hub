#!/usr/bin/env bash
# Jalankan TIF Hub dengan batas upload yang cukup untuk foto proyek
cd "$(dirname "$0")"
php -d upload_max_filesize=10M -d post_max_size=40M -d memory_limit=256M -S localhost:8001 -t public
