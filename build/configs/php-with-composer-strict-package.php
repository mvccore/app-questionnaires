<?php

$config = [
	'sourcesDir'				=> __DIR__ . '/../../development',
	'releaseFile'				=> __DIR__ . '/../../release/index.php',
	// do not include script or file, where it's relative path from sourceDir match any of these rules:
	'excludePatterns'			=> [

		// Common excludes for every MvcCore app using composer:
		"#/\.#",										// Everything started with '.' (.git, .htaccess ...)
		"#^/web\.config#",								// Microsoft IIS .rewrite rules
		"#^/Var/Logs/.*#",								// App development logs
		"#composer(.*)(json|lock)#",					// composer.json, composer.lock, composer.dev.json and  composer.dev.lock
		"#LICEN(C|S)E\.(txt|TXT|md|MD)#",				// libraries licence files
		"#\.(bak|bat|cmd|sh|md|phpt|phpproj|phpproj.user)$#",

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
		"#/declarations/([a-z]*).css$",
		"#/MvcCore/Ext/Forms/assets/(.*)#",
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
