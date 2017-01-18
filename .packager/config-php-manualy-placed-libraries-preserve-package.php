<?php

$config = array(
	'sourcesDir'			=> __DIR__ . '/development',
	'releaseFile'			=> __DIR__ . '/release/index.php',
	'excludePatterns'		=> array(
		// Common excludes for every application:
		"^/\.htaccess",			// Apache .rewrite rules
		"^/web.config",			// Microsoft IIS .rewrite rules
		"^/Var/Logs/(.*)$",		// App development logs
		".bak$",				// Anything to backup
		".bat$",				// Instalation bat files
		// Source static files and libraries to generate minified results (optional):
		"^/Libs/Minify",		// Exclude libraries to minify HTML and CSS
		"^/Libs/JSMin.php",		// Exclude library to minify JS
		// Exclude source css and js
		"^/static/js",			
		"^/static/css",
		"/declarations/([a-z]*).css$",
		"^/Libs/SimpleForm/simple-form.js",
		"^/Libs/SimpleForm/fields/",
		// Remove Debug library only after application is successfly packed (optional, https://tracy.nette.org/):
		"^/Libs/Tracy",
		"^/Libs/MvcCoreExt/Tracy",
		// do not pack any source xml files - to manipulate with questionnaires in future
		// not possible for PHAR packing!!!
		"^/Var/Questionnaires/(.*)",
	),
	'includePatterns'		=> array(
		"^/Libs/Minify/HTML",
	),
	'stringReplacements'	=> array(
		// Before packing - run MvcCore app in single file links mode to generate proper assets in tmp directory
		// after all tmp assets with single file mode are generated - change this boolean back
		'$app->Run(1);'		=> '$app->Run();',
		// Remove Debug library only after application is successfly packed (optional):
		"->SetDebugClass(MvcCoreExt_Tracy::class)"	=> "",
	),
	'minifyTemplates'		=> 1,// Remove non-conditional comments and whitespaces
	'minifyPhp'				=> 1,// Remove comments and whitespaces
);