@echo off
: This scripts calls doctrine-migrations tool
: Example:
:  bin/doctrine-migrations --no-interaction migrations:migrate --allow-no-migration

php "%~dp0..\vendor\bin\doctrine-migrations" %* || exit /B 1
