<?php

	@include_once('vendor/autoload.php');
	
	$app = MvcCore::GetInstance();

	App_Bootstrap::Init();
	
	$app->Run();