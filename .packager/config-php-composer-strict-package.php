<?php

$config = array(
	'sourcesDir'			=> __DIR__ . '/../development',
	'releaseFile'			=> __DIR__ . '/../release/index.php',
	'excludePatterns'		=> array(
		
		// Common excludes for every MvcCore app using composer:
		"/\.",										// Everything started with '.' (.git, .htaccess ...)
		"^/web\.config",							// Microsoft IIS .rewrite rules
		"^/Var/Logs/.*",							// App development logs
		"composer\.(json|lock)",					// composer.json and composer.lock
		"LICEN(C|S)E\.(txt|TXT|md|MD)",				// libraries licence files
		"\.(bak|BAK|bat|BAT|sh|SH|md|MD|phpt|PHPT)$",
		
		// Exclude specific PHP libraries
		"^/vendor/composer/.*",						// composer itself
		"^/vendor/autoload\.php",					// composer autoload file
		"^/vendor/mvccore/mvccore/src/startup\.php",// mvccore autoload file
		"^/vendor/tracy/.*",						// tracy library (https://tracy.nette.org/)
		"^/vendor/mvccore/ext-debug-tracy.*",		// mvccore tracy adapter and tracy panel extensions
		"^/vendor/nette/safe-stream.*",				// nette safe stream used to complete assets in cache

		// Every file in mobile detect library directory,
		// but only one file will be included by overide pattern later
		"^/vendor/mobiledetect/.*",
		// Everything in library to minify HTML/JS/CSS minify,
		// but only two files will be included by overide pattern later
		"^/vendor/mrclay/.*",			

		// Exclude source css and js files
		"^/static/js",
		"^/static/css",
		"/declarations/([a-z]*).css$",
		"/MvcCore/Ext/Form/mvccore-form.js",
		"/MvcCore/Ext/Form/fields/",
	),
	// include paterns overides exclude patterns:
	'includePatterns'		=> array(
		"^/vendor/mrclay/minify/min/lib/Minify/HTML",
		"^/vendor/mobiledetect/mobiledetectlib/Mobile_Detect\.php$",
	),
	'stringReplacements'	=> array(
		// Before packing - run MvcCore app in single file links mode to generate proper assets in tmp directory
		// after all tmp assets with single file mode are generated - change this boolean back
		'$app->Run(1);'		=> '$app->Run();',
		// Remove Debug library only after application is successfly packed (optional):
		'->SetDebugClass(\MvcCore\Ext\Debug\Tracy::class)'	=> '',
	),
	'minifyTemplates'		=> 1,// Remove non-conditional comments and whitespaces
	'minifyPhp'				=> 1,// Remove comments and whitespaces
);