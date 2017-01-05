<?php

class App_Views_Helpers_Assets
{
	/**
	 * Default link group name
	 * 
	 * @const string
	 */
	const GROUP_NAME_DEFAULT   = 'default';
	
	/**
	 * Date format for ?_fmd param timestamp in admin development mode
	 * 
	 * @const string
	 */
	const FILE_MODIFICATION_DATE_FORMAT = 'Y-m-d_H-i-s';
	
	/**
	 * Simple app view object
	 * 
	 * @var MvcCore_View
	 */
	protected $view;
	
	/**
	 * Called $_linksGroupContainer index throw helper function Css() or Js()
	 * 
	 * @var string
	 */
	protected $actualGroupName = '';
	
	/**
	 * Stream wrapper for actual file save operations (http://php.net/stream_wrapper_register)
	 * 
	 * @var string
	 */
	protected $streamWrapper = '';

	/**
	 * Global options about joining and minifying which can bee overwrited by single settings throw calling for eeample: append() method as another param
	 *
	 * @var array
	 */
	protected static $globalOptions = array(
		'jsJoin'		=> 0,
		'jsMinify'		=> 0,
		'cssJoin'		=> 0,
		'cssMinify'		=> 0,
		'tmpDir'		=> '/Var/Tmp',
		'fileChecking'	=> 'filemtime',
	);
	
	/**
	 * Application root directory from request object
	 * 
	 * @var string
	 */
	protected static $appRoot = '';
	
	/**
	 * Relative path to store joined and minified files from application root directory
	 * 
	 * @var string
	 */
	protected static $tmpDir = '';
	
	/**
	 * Base not compiled url path from localhost if necessary
	 * 
	 * @var string
	 */
	protected static $basePath = NULL;

	/**
	 * If true, all messages are logged on hard drive, all exceptions are thrown
	 * 
	 * @var boolean
	 */
	protected static $logingAndExceptions = TRUE;

	/**
	 * If true, all assets sources existences are checked and temporary files are rendered
	 * 
	 * @var boolean
	 */
	protected static $fileCheckingAndRendering = TRUE;

	/**
	 * If true, method AssetUrl in all css files returns to index.php?controller=controller&action=asset&path=...
	 * 
	 * @var boolean
	 */
	protected static $assetUrlCompletion = FALSE;

	/**
	 * Hash completed as md5(json_encode()) from self::$globalOptions
	 * 
	 * @var string
	 */
	protected static $systemConfigHash = '';

	/**
	 * Insert a MvcCore_View in each helper constructing
	 */
	public function __construct ($view) {
		$this->view = $view;
		$request = $this->view->GetController()->GetRequest();
		self::$appRoot = $request->appRoot;
		if (is_null(self::$basePath)) self::$basePath = $request->basePath;
		self::$logingAndExceptions = MvcCore::GetEnvironment() == 'development';
		$mvcCoreCompiledMode = MvcCore::GetCompiled();
		self::$fileCheckingAndRendering = substr($mvcCoreCompiledMode, 0, 3) != 'PHP' && $mvcCoreCompiledMode != 'PHAR';
		self::$systemConfigHash = md5(json_encode(self::$globalOptions));
		if ($mvcCoreCompiledMode && substr($mvcCoreCompiledMode, 0, 12) != 'PHP_PRESERVE' && $mvcCoreCompiledMode != 'PHP_STRICT_HDD') {
			self::$assetUrlCompletion = TRUE;
		}
	}

	/**
	 * Set global static $basePath to load assets from any static cdn domain or any other place
	 *
	 * @param string $basePath
	 * 
	 * @return void
	 */
	public static function SetBasePath ($basePath) {
		self::$basePath = $basePath;
	}
	
	/**
	 * Set global static options about minifying and joining together which can bee overwrited by single settings throw calling for eeample: append() method as another param
	 *
	 * @param array $options whether or not to auto escape output
	 * 
	 * @return void
	 */
	public static function SetGlobalOptions($options = array()) {
		foreach ($options as $key => $value) {
			self::$globalOptions[$key] = $value;
		}
	}

	/**
	 * Returns file modification imprint by global settings - by md5_file() or by filemtime() - always as a string
	 *
	 * @param string $fullPath
	 *
	 * @return string
	 */
	protected static function getFileImprint ($fullPath) {
		$fileChecking = self::$globalOptions['fileChecking'];
		if ($fileChecking == 'filemtime') {
			return filemtime($fullPath);
		} else {
			return (string) call_user_func($fileChecking, $fullPath);
		}
	}
	
	/**
	 * Creates a MvcCore Url - always from one place
	 *
	 * @param  string $path
	 * 
	 * @return string
	 */
	public function AssetUrl ($path = '') {
		$result = '';
		/**
		 *	Feel free to change assets url completion to any way.
		 *	There could be typically only: "$result = self::$basePath . $path;",
		 *	but if you want to complete url for assets on hard drive or 
		 *	to any other cdn place, use App_Views_Helpers_Assets::SetBasePath($cdnBasePath);
		 */
		if (self::$assetUrlCompletion) {
			// for MvcCore::GetCompiled() equal to: 'PHAR', 'SFU', 'PHP_STRICT_PACKAGE'
			$result = $this->view->AssetUrl($path);
		} else {
			// for MvcCore::GetCompiled() equal to: '' (development), 'PHP_PRESERVE_PACKAGE', 'PHP_PRESERVE_HDD', 'PHP_STRICT_HDD'
			$result = self::$basePath . $path;
		}
		return $result;
	}
	
	/**
	 * Look for every item to render if there is any 'doNotMinify' record to render item separately
	 * 
	 * @param array $items 
	 * 
	 * @return array[] $itemsToRenderMinimized $itemsToRenderSeparately
	 */
	protected function filterItemsForNotPossibleMinifiedAndPossibleMinifiedItems ($items) {
		$itemsToRenderMinimized = array();
		$itemsToRenderSeparately = array(); // some configurations is not possible to render together and minimized
		// go for every item to complete existing combinations in attributes
		foreach ($items as $item) {
			$itemArr = array_merge((array) $item, array());
			unset($itemArr['path']);
			if (isset($itemArr['render'])) unset($itemArr['render']);
			if (isset($itemArr['external'])) unset($itemArr['external']);
			$renderArrayKey = md5(json_encode($itemArr));
			if ($itemArr['doNotMinify']) {
				if (isset($itemsToRenderSeparately[$renderArrayKey])) {
					$itemsToRenderSeparately[$renderArrayKey][] = $item;
				} else {
					$itemsToRenderSeparately[$renderArrayKey] = array($item);
				}
			} else {
				if (isset($itemsToRenderMinimized[$renderArrayKey])) {
					$itemsToRenderMinimized[$renderArrayKey][] = $item;
				} else {
					$itemsToRenderMinimized[$renderArrayKey] = array($item);
				}
			}
		}
		return array(
			$itemsToRenderMinimized,
			$itemsToRenderSeparately,
		);
	}
	
	/**
	 * Add to href url file modification param by original file
	 *
	 * @param  string $url
	 * @param  string $path
	 * 
	 * @return string
	 */
	protected function addFileModificationTimeToHrefUrl ($url, $path) {
		$questionMarkPos = strpos($url, '?');
		$separator = ($questionMarkPos === FALSE) ? '?' : '&';
		$strippedUrl = $questionMarkPos !== FALSE ? substr($url, $questionMarkPos) : $url ;
		$srcPath = $this->getAppRoot() . substr($strippedUrl, strlen(self::$basePath));
		$fileMTime = intval(filemtime($srcPath));
		$url .= $separator . '_fmt=' . date(
			self::FILE_MODIFICATION_DATE_FORMAT,
			$fileMTime
		);
		return $url;
	}
	
	/**
	 * Get indent string
	 *
	 * @param string|int $indent
	 * 
	 * @return string
	 */
	protected function getIndentString($indent = 0) {
		$indentStr = '';
		if (is_numeric($indent)) {
			$indInt = intval($indent);
			if ($indInt > 0) {
				$i = 0;
				while ($i < $indInt) {
					$indentStr .= "\t";
					$i += 1;
				}
			}
		} else if (is_string($indent)) {
			$indentStr = $indent;
		}
		return $indentStr;
	}

	/**
	 * Return and store application document root from controller view request object
	 * 
	 * @return string
	 */
	protected function getAppRoot() {
		return self::$appRoot;
	}

	/**
	 * Return and store application document root from controller view request object
	 * 
	 * @return string
	 */
	protected function getTmpDir() {
		if (!self::$tmpDir) {
			$tmpDir = $this->getAppRoot() . self::$globalOptions['tmpDir'];
			if (!MvcCore::GetCompiled()) {
				if (!is_dir($tmpDir)) mkdir($tmpDir, 0777, TRUE);
				if (!is_writable($tmpDir)) {
					try {
						@chmod($tmpDir, 0777);
					} catch (Exception $e) {
						throw new Exception('[App_Views_Helpers_Assets] ' . $e->getMessage());
					}
				}
			}
			self::$tmpDir = $tmpDir;
		}
		return self::$tmpDir;
	}

	/**
	 * Save atomicly file content in full path by 1 MB to not overflow any memory limits
	 * 
	 * @param string $fullPath 
	 * @param string $fileContent 
	 * 
	 * @return void
	 */
	protected function saveFileContent ($fullPath = '', & $fileContent = '') {
		$streamWrapper = '';
		// https://github.com/nette/safe-stream/blob/master/src/SafeStream/SafeStream.php
		$netteSafeStreamClass = 'Nette_Utils_SafeStream';
		$netteSafeStreamExists = class_exists($netteSafeStreamClass);
		if (self::$fileCheckingAndRendering) {
			if ($netteSafeStreamExists) {
				$netteSafeStreamProtocol = constant($netteSafeStreamClass.'::PROTOCOL');
				(new ReflectionMethod($netteSafeStreamClass, 'register'))->invoke(NULL);
				$streamWrapper = $netteSafeStreamProtocol . '://';
			}
		}
		$fw = fopen($streamWrapper . $fullPath, 'w');
		$index = 0;
		$bufferLength = 1048576; // 1 MB
		$buffer = '';
		while ($buffer = mb_substr($fileContent, $index, $bufferLength)) {
			fwrite($fw, $buffer);
			$index += $bufferLength;
		}
		fclose($fw);
		@chmod($fullPath, 0766);
		if (self::$fileCheckingAndRendering) {
			if ($netteSafeStreamExists) stream_wrapper_unregister($netteSafeStreamProtocol);
		}
	}

	/**
	 * Log any render messages with optional log file name
	 *
	 * @param string $msg
	 * @param string $logType
	 * 
	 * @return void
	 */
	protected function log ($msg = '', $logType = 'debug') {
		if (self::$logingAndExceptions) {
			if (class_exists('Debug')) {
				Debug::log('[' . get_class($this) . '] ' . $msg, $logType);
			} else {
				var_dump($msg);
			}
		}
	}

	/**
	 * Throw exception with given message with actual helper class name before
	 *
	 * @param string $msg
	 * 
	 * @throws Exception text by given message
	 */
	protected function exception ($msg) {
		if (self::$logingAndExceptions) {
			throw new Exception('[' . get_class($this) . '] ' . $msg);
		}
	}

	/**
	 * Render given exception
	 *
	 * @param Exception $e
	 * 
	 * @throws Exception
	 */
	protected function exceptionHandler ($e) {
		if (self::$logingAndExceptions) {
			if (class_exists('Debug')) {
				Debug::_exceptionHandler($e);
			} else {
				throw $e;
			}
		}
	}

	/**
	 * Complete items group tmp directory file name by group source files info
	 * 
	 * @param array   $filesGroupInfo 
	 * @param boolean $minify 
	 * 
	 * @return string
	 */
	protected function getTmpFileFullPathByPartFilesInfo ($filesGroupInfo = array(), $minify = FALSE, $extension = '') {
		/*
		echo '<pre>';
		var_dump(array($this->getTmpDir(), $filesGroupInfo, $minify, $extension));
		echo '</pre>';
		*/
		return implode('', array(
			$this->getTmpDir(),
			'/' . ($minify ? 'minified' : 'rendered') . '_' . $extension . '_',
			md5(implode(',', $filesGroupInfo) . '_' . $minify),
			'.' . $extension
		));
	}
	
}