@echo off

:: standard way to create phar archive (much slower)
::php phar.php

:: advanced way to create single php file with base64 encoded static files (much faster)
php make-php.php

@pause
