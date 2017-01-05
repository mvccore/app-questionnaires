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
		"^/static/js/front/node_modules",
		"-debug.([csj]*)$",
		"_references.js$",
		// Remove Debug library only after application is successfly packed (optional):
		"^/Libs/Nette/Debug",	// https://tracy.nette.org/
		// do not pack any source xml files - to manipulate with questionnaires in future
		// not possible for PHAR packing!!!
		"^/Var/Questionnaires/(.*)",
	),
	'stringReplacements'	=> array(
		// Before packing - run MvcCore app in single file links mode to generate proper assets in tmp directory
		// after all tmp assets with single file mode are generated - change this boolean back
		'MvcCore::Run(1);'		=> 'MvcCore::Run();',
		// Remove Debug library only after application is successfly packed (optional):
		"Nette_DebugAdapter::Init(MvcCore::GetEnvironment() == 'development');"	=> "MvcCore::SetEnvironment('production');",
	),
	'minifyTemplates'		=> 1,// Remove non-conditional comments and whitespaces
	'minifyPhp'				=> 1,// Remove comments and whitespaces
);