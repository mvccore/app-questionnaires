<?php

class Nette_DebugAdapter
{
	/**
	 * Valie email address to recieve error messages
	 */
	private static $_debugRecepient = 'tomflidr@gmail.com';
	
	/**
	 * Relative path from document root directory.
	 * If directory bellow doesn't exist, it will be created at first use
	 */
	private static $_logDirectory = '/Var/Logs';

	/**
	 * @var boolean
	 */
	private static $_initialized = FALSE;

	/**
	 * Initialization of Nette\Debug tool
	 * @param $debug			boolean		optional	set TRUE on development mode
	 * @param $logDirectory		string		optional 	equivalent to static self::$_logDirectory
	 * @param $debugRecepient	string		optional	equivalent to static self::$_debugRecepient
	 * @void
	 */
	public static function Init ($debug = FALSE, $logDirectory = '', $debugRecepient = '') {
		self::_includeFiles();
	
		$indexFilePath = str_replace('\\', '/', $_SERVER['SCRIPT_FILENAME']);
		$appRootPath = mb_substr($indexFilePath, 0, mb_strrpos($indexFilePath, '/'));
		$logDirectory = $appRootPath . ($logDirectory ? $logDirectory : self::$_logDirectory);
		$debugRecepient = $debugRecepient ? $debugRecepient : self::$_debugRecepient;

		if (class_exists('MvcCore') && MvcCore::GetCompiled()) {
			$logDirectory = '';
		} else {
			if (!is_dir($logDirectory)) mkdir($logDirectory, 0777, TRUE);
			if (!is_writable($logDirectory)) {
				try {
					chmod($logDirectory, 0777);
				}
				catch (Exception $e) {
					die('[Nette_DebugAdapter] ' . $e->getMessage());
				}
			}
		}

		Debug::enable(!$debug, $logDirectory, $debugRecepient);
		Debug::$editor .= '&editor=MSVS2015';

		self::_initDebugPanels();
		self::_initGlobalShorthands();
		self::$_initialized = TRUE;
	}
	public static function Shutdown () {
		Debug::_shutdownHandler();
	}
	private static function _includeFiles () {
		include_once(__DIR__.'/Debug/Debug.php');
		include_once(__DIR__.'/Debug/IncludePanel.php');
		include_once(__DIR__.'/Debug/SessionPanel.php');
	}
	private static function _initDebugPanels () {
		Debug::addPanel(new IncludePanel);
		Debug::addPanel(new SessionPanel);
	}
	private static function _initGlobalShorthands () {
		function x($a = NULL, $b = NULL){
			return Debug::dump($a, true, $b);
		};
		function xxx($a = NULL, $b = NULL){
			$args = func_get_args();
			if (count($args) === 0) {
				throw new Exception ("Stopped.");
			} else {
				header('Content-Type: text/html; charset=utf-8');
				echo Debug::dump($a, TRUE, $b);
			}
			die();
		};
	}
}