<?php

	include_once('./../../libraries/packager/src/Packager/Phar.php');

	include_once('./config.php');

	Packager_Phar::Create($config)->Run();