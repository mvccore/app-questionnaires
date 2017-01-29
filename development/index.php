<?php

	@include_once('vendor/autoload.php');

	$app = \MvcCore::GetInstance();

	\App\Bootstrap::Init();
	
	$app->Run(1);