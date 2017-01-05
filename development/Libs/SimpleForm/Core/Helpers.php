<?php

require_once('/../../SimpleForm.php');
require_once('Base.php');

class SimpleForm_Core_Helpers
{
	const CTRL_VIEW_PROVIDER_METHOD = 0;
	const CTRL_VIEW_PROVIDER_PROPERTY = 1;
	const SESSION_PROVIDER_INSTANCE = 0;
	const SESSION_PROVIDER_STATIC = 1;
	/**
	 * @var array<String>
	 */
	public static $ControllerViewProvider = array(
		'type'		=> self::CTRL_VIEW_PROVIDER_METHOD,
		'getter'	=> 'GetView',
	);
	/**
	 * @var array<String>
	 */
	public static $SessionProvider = array(
		// to get simple $_SESSION records - use:
		'type'		=> self::SESSION_PROVIDER_STATIC,
		'callable'	=> array(__CLASS__, 'getSimpleSessionRecord'),
		'expirator'	=> '',
		'expiration'=> 0,
		
		/**
		 * OTHER EXAMPLES:
		*/

		/* MvcCore_Session - use:
		'type'		=> self::SESSION_PROVIDER_STATIC,
		'callable'	=> array('MvcCore_Session', 'GetNamespace'),
		'expirator'	=> 'setExpirationSeconds',
		'expiration'=> 32872500,  // (60 * 60 * 24 * 365.25) -> year
		*/
		
		/* Zend_Session - use:
		'type'		=> self::SESSION_PROVIDER_INSTANCE,
		'class'		=> 'Zend_Session_Namespace',
		'expirator'	=> 'setExpirationSeconds',
		'expiration'=> 32872500,  // (60 * 60 * 24 * 365.25) -> year
		*/
	);
	protected static $sessionData = NULL;
	protected static $sessionCsrf = NULL;
	protected static $sessionErrors = NULL;
	protected static $yearSeconds = 32872500; // (60 * 60 * 24 * 365.25);
	public static function GetControllerView (& $controller) {
		$result = NULL;
		$type = static::$ControllerViewProvider['type'];
		$getter = static::$ControllerViewProvider['getter'];
		if ($type == self::CTRL_VIEW_PROVIDER_PROPERTY) {
			$result = $controller->{$getter};
		} else if ($type == self::CTRL_VIEW_PROVIDER_METHOD) {
			$result = $controller->{$getter}();
		}
		return $result;
	}
	/* session ***********************************************************************/
	public static function GetSessionData ($formId = '') {
		$sessionData = & static::setUpSessionData();
		if ($formId && isset($sessionData->$formId)) {
			$rawResult = $sessionData->$formId;
			return $rawResult;
		} else {
			return array();
		}
	}
	public static function GetSessionCsrf ($formId = '') {
		$sessionCsrf = & static::setUpSessionCsrf();
		if ($formId && isset($sessionCsrf->$formId)) {
			$rawResult = $sessionCsrf->$formId;
			return $rawResult;
		} else {
			return array();
		}
	}
    public static function GetSessionErrors ($formId = '') {
		$sessionErrors = & static::setUpSessionErrors();
		if ($formId && isset($sessionErrors->$formId)) {
			$rawResult = $sessionErrors->$formId;
			return $rawResult;
		} else {
			return array();
		}
	}
	public static function SetSessionData ($formId = '', $data = array()) {
		$sessionData = & static::setUpSessionData();
		if ($formId) $sessionData->$formId = $data;
	}
	public static function SetSessionCsrf ($formId = '', $csrf = array()) {
		$sessionCsrf = & static::setUpSessionCsrf();
		if ($formId) $sessionCsrf->$formId = $csrf;
	}
	public static function SetSessionErrors ($formId = '', $errors = array()) {
		$sessionErrors = & static::setUpSessionErrors();
		if ($formId) $sessionErrors->$formId = $errors;
	}
	protected static function & setUpSessionData () {
		if (static::$sessionData == NULL) static::$sessionData = static::getSessionNamespace('SimpleForm_Data');
		return static::$sessionData;
	}
	protected static function & setUpSessionCsrf () {
		if (static::$sessionCsrf == NULL) static::$sessionCsrf = static::getSessionNamespace('SimpleForm_Csrf');
		return static::$sessionCsrf;
	}
	protected static function & setUpSessionErrors () {
		if (self::$sessionErrors == NULL) static::$sessionErrors = static::getSessionNamespace('SimpleForm_Errors');
		return self::$sessionErrors;
	}
	protected static function & getSessionNamespace ($namespace) {
		$type = static::$SessionProvider['type'];
		if ($type == self::SESSION_PROVIDER_INSTANCE) {
			$class = static::$SessionProvider['class'];
			$result = new $class($namespace);
		} else if ($type == self::SESSION_PROVIDER_STATIC) {
			$result = call_user_func(static::$SessionProvider['callable'], $namespace);
		}
		$expirator = static::$SessionProvider['expirator'];
		$expiration = static::$SessionProvider['expiration'];
		// do not use this, because all page elements should be requested throw php script in MvcCore package, including all assets
		// $result->SetExpirationHoops(1);
		if ($expirator && $expiration) $result->$expirator($expiration);
		return $result;
	}
	protected static function & getSimpleSessionRecord ($namespace) {
		if (!(isset($_SESSION[$namespace]) && !is_null($_SESSION[$namespace]))) {
			$_SESSION[$namespace] = new stdClass;
		}
		return $_SESSION[$namespace];
	}
	/* common helpers ********************************************************************/
	public static function ValidateMaxPostSizeIfNecessary(SimpleForm & $form) {
		if (strtolower($form->Method) != 'post') return;
		$maxSize = ini_get('post_max_size');
		if (empty($_SERVER['CONTENT_LENGTH'])) {
			$form->AddError(
				sprintf(SimpleForm_Core_Base::$DefaultMessages[SimpleForm_Core_Base::EMPTY_CONTENT], $maxSize)
			);
			$form->Result = SimpleForm::RESULT_ERRORS;
		}
		$units = array('k' => 10, 'm' => 20, 'g' => 30);
		if (isset($units[$ch = strtolower(substr($maxSize, -1))])) {
			$maxSize <<= $units[$ch];
		}
		if ($maxSize > 0 && isset($_SERVER['CONTENT_LENGTH']) && $maxSize < $_SERVER['CONTENT_LENGTH']) {
			$form->AddError(
				sprintf(SimpleForm_Core_Base::$DefaultMessages[SimpleForm_Core_Base::MAX_POST_SIZE], $maxSize)
			);
			$form->Result = SimpleForm::RESULT_ERRORS;
		}
	}
}
