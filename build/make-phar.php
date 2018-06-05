<?php

	include_once(__DIR__.'/../vendor/autoload.php');

	include_once(__DIR__.'/configs/phar-with-composer.php');

	Packager_Phar::Create($config)->Run();