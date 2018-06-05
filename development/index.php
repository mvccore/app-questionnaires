<?php

	@include_once('vendor/autoload.php');

	$app = \MvcCore\Application::GetInstance();

	\App\Bootstrap::Init();
	
	$app->Run();
