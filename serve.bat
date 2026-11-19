@echo off
cd /d "%~dp0"
set PHP_BIN=php
if exist C:\PHP84\php.exe set PHP_BIN=C:\PHP84\php.exe
echo TIF Hub berjalan di http://localhost:8001  (Ctrl+C untuk berhenti)
"%PHP_BIN%" -d upload_max_filesize=10M -d post_max_size=40M -d memory_limit=256M -S localhost:8001 -t public
