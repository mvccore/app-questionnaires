<?php

	include_once(__DIR__.'/../vendor/mvccore/packager/src/Packager/Php.php');


	// to pack PHP scripts, templates and all static files (CSS/JS/images and fonts),
	// use this config bellow and:
	//		set: ...->SetPhpFileSystemMode(Packager_Php::FS_MODE_STRICT_PACKAGE)
	include_once(__DIR__.'/configs/php-with-composer-strict-package.php');


	// to pack PHP scripts and templates but without any static files,
	// use this config bellow and:
	//		set: ...->SetPhpFileSystemMode(Packager_Php::FS_MODE_PRESERVE_PACKAGE)
	//		and follow copying instructions inside config:
	//include_once(__DIR__.'/configs/php-with-composer-preserve-package.php');


	// to pack PHP scripts and templates but without any static files,
	// use this config bellow and:
	//		set: ...->SetPhpFileSystemMode(Packager_Php::FS_MODE_PRESERVE_HDD)
	//		and follow copying instructions inside config:
	//include_once(__DIR__.'/configs/php-with-composer-preserve-hdd.php');


	// to pack only PHP scripts without any static files and any templates,
	// use this config bellow and
	//		set: ...->SetPhpFileSystemMode(Packager_Php::FS_MODE_STRICT_HDD)
	//		and follow copying instructions inside config:
	//include_once(__DIR__.'/configs/php-with-composer-strict-hdd.php');


	Packager_Php::Create($config)
		->SetPhpFileSystemMode(Packager_Php::FS_MODE_STRICT_PACKAGE)
		->Run();