<?php

	include_once('vendor/autoload.php');

	include_once('.packager/config-phar-composer.php');

	Packager_Phar::Create($config)->Run();