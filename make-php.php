<?php

	include_once('vendor/mvccore/packager/src/Packager/Php.php');


	include_once('.packager/config-php-composer-preserve-package.php');
	//include_once('.packager/config-php-composer-strict-package.php');


	Packager_Php::Create($config)
		//->PrintFilesToPack()
		->SetPhpFileSystemMode(Packager_Php::FS_MODE_PRESERVE_PACKAGE)
		->KeepPhpFunctions(
			//'require',
			//'include',
			//'DirectoryIterator',
			//'parse_ini_file'
		)
		->Run();