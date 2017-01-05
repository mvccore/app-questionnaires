<?php
	
	include_once('./../../libraries/packager/src/Packager/Php.php');

	include_once('./config.php');

	Packager_Php::Create($config)
		->SetPhpFileSystemMode(Packager_Php::FS_MODE_PRESERVE_PACKAGE)
		->KeepPhpFunctions(
			//'require',
			//'include',
			//'DirectoryIterator',
			//'parse_ini_file'
		)
		->Run();