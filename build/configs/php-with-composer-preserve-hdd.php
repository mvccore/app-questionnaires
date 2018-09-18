<?php

// If you want to use this config, you need to copy manualy everything 'from' => 'to':
// - '/development/static/fonts'	=> '/release/static/fonts'
// - '/development/static/img'		=> '/release/static/img'
// - '/development/Var/Tmp'			=> '/release/Var/Tmp'
// and you need to uncomment line 12 in Bootstrap.php
// before compilation to generate css/js files properly in tmp

ini_set('memory_limit', '128M');
ini_set('max_execution_time', 300);

$config = [
	'sourcesDir'				=> __DIR__ . '/../../development',
	'releaseFile'				=> __DIR__ . '/../../release/index.php',
	// do not include script or file, where it's relative path from sourceDir match any of these rules:
	'excludePatterns'			=> [
		// Common excludes for every MvcCore app using composer:
		"#/\.#",										// Everything started with '.' (.git, .htaccess ...)
		"#\.(bak|bat|cmd|sh|md|txt|json|lock|phpt|config|htaccess|htpasswd|phpproj|phpproj.user)$#i",
		"#^/App/config.ini#",							// App system config
		"#^/Var/Logs/.*#",								// App development logs

		// Exclude specific PHP libraries
		"#^/vendor/composer/.*#",						// composer itself
		"#^/vendor/autoload\.php$#",					// composer autoload file
		"#^/vendor/mvccore/mvccore/src/startup\.php$#",	// mvccore autoload file
		"#^/vendor/tracy/.*#",							// tracy library (https://tracy.nette.org/)
		"#^/vendor/mvccore/ext-debug-tracy.*#",			// mvccore tracy adapter and all tracy panel extensions
		"#^/vendor/nette/safe-stream.*#",				// nette safe stream used to complete assets in cache
		"#^/vendor/mobiledetect/.*#",					// Mobile detect lib, the only required class included later
		"#^/vendor/mrclay/.*#",							// HTML/JS/CSS minify library

		// Exclude source css and js files, use only what is generated in '/Var/Tmp' dir
		"#^/static/js#",
		"#^/static/css#",
		"#/declarations/([a-z]*).css$#",
		"#/MvcCore/Ext/Forms/assets/(.*)#",
		"#^/Var/Tmp/.*#",

		// do not pack any source xml files - to manipulate with questionnaires in future
		// not possible for PHAR packing!!!
		"#^/Var/Questionnaires/(.*)#",
		"#^/Var/Documents/(.*)#",
	],
	// include all scripts or files, where it's relative path from sourceDir match any of these rules:
	// (include paterns always overides exclude patterns)
	'includePatterns'		=> [
		"#^/vendor/mrclay/minify/min/lib/Minify/HTML#",
		"#^/vendor/mobiledetect/mobiledetectlib/Mobile_Detect\.php$#",
	],
	// process simple strings replacements on all readed PHP scripts before saving into result package:
	// (replacements are executed before configured minification in RAM, they don't affect anythin on hard drive)
	'stringReplacements'	=> [
		// Switch MvcCore application back from SFU mode to automatic compile mode detection
		'->Run(1);'		=> '->Run();',
		// Remove tracy debug library extension usage (optional):
		"class_exists('\MvcCore\Ext\Debugs\Tracy')"	=> 'FALSE',
	],
	'minifyTemplates'		=> 1,// Remove non-conditional comments and whitespaces
	'minifyPhp'				=> 1,// Remove comments and whitespaces
];
