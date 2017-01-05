<?php

require_once('/../../SimpleForm.php');
require_once('/../Button.php');
require_once('Helpers.php');
require_once('Field.php');
require_once('Validator.php');
require_once('View.php');

class SimpleForm_Core_Base
{
	const METHOD_DELETE = 'delete';
	const METHOD_GET    = 'get';
	const METHOD_POST   = 'post';
	const METHOD_PUT    = 'put';
	
	const ENCTYPE_URLENCODED = 'application/x-www-form-urlencoded';
	const ENCTYPE_MULTIPART  = 'multipart/form-data';
	
	const HTML_IDS_DELIMITER = '_';
	
	const EQUAL = ':equal',
		NOT_EQUAL = ':notEqual',
		REQUIRED = ':required',
		INVALID_FORMAT = ':invalidFormat',
		INVALID_CHARS = ':invalidChars',
		EMPTY_CONTENT = ':empty',
		CSRF = ':csrf',
		// text
		MIN_LENGTH = ':minLength',
		MAX_LENGTH = ':maxLength',
		LENGTH = ':length',
		EMAIL = ':email',
		URL = ':url',
		NUMBER = ':number',
		INTEGER = ':integer',
		FLOAT = ':float',
		PHONE = ':phone',
		ZIP_CODE = ':zipCode',
		TAX_ID = ':taxId',
		VAT_ID = ':varId',
		GREATER = ':greater',
		LOWER = ':lower',
		RANGE = ':range',
		// file upload
		MAX_FILE_SIZE = ':fileSize',
		MAX_POST_SIZE = ':maxPostSize',
		IMAGE = ':image',
		MIME_TYPE = ':mimeType',
		// other
		VALID = ':valid',
		CHOOSE_MIN_OPTS = ':chooseMinOpts',
		CHOOSE_MAX_OPTS = ':chooseMaxOpts',
		CHOOSE_MIN_OPTS_BUBBLE = ':chooseMinOptsBubble',
		CHOOSE_MAX_OPTS_BUBBLE = ':chooseMaxOptsBubble';

	public static $DefaultMessages = array(
		self::EQUAL					=> "Field '{0}' requires exact value: '{1}'.",
		self::NOT_EQUAL				=> "Value for field '{0}' should not be '{1}'.",
		self::REQUIRED				=> "Field '{0}' is required.",
		self::INVALID_FORMAT		=> "Field '{0}' has invalid format ('{1}').",
		self::INVALID_CHARS			=> "Field '{0}' contains invalid characters.",
		self::EMPTY_CONTENT			=> "Sent data are empty.",
		self::CSRF					=> "Form hash expired, please submit the form again.",
		self::MIN_LENGTH			=> "Field '{0}' requires at least {1} characters.",
		self::MAX_LENGTH			=> "Field '{0}' requires no more than {1} characters.",
		self::LENGTH				=> "Field '{0}' requires a value between {1} and {2} characters long.",
		self::EMAIL					=> "Field '{0}' requires a valid email address.",
		self::URL					=> "Field '{0}' requires a valid URL.",
		self::NUMBER				=> "Field '{0}' requires a valid number.",
		self::INTEGER				=> "Field '{0}' requires a valid integer.",
		self::FLOAT					=> "Field '{0}' requires a valid float number.",
		self::PHONE					=> "Field '{0}' requires a valid phone number.",
		self::ZIP_CODE				=> "Field '{0}' requires a valid zip code.",
		self::TAX_ID				=> "Field '{0}' requires a valid TAX ID.",
		self::VAT_ID				=> "Field '{0}' requires a valid VAR ID.",
		self::GREATER				=> "Field '{0}' requires a value greater than {1}.",
		self::LOWER					=> "Field '{0}' requires a value lower than {1}.",
		self::RANGE					=> "Field '{0}' requires a value between {1} and {2}.",
		self::MAX_FILE_SIZE			=> "The size of the uploaded file can be up to {0} bytes.",
		self::MAX_POST_SIZE			=> "The uploaded data exceeds the limit of {0} bytes.",
		self::IMAGE					=> "The uploaded file has to be image in format JPEG, GIF or PNG.",
		self::MIME_TYPE				=> "The uploaded file is not in the expected file format.",
		self::VALID					=> "Field '{0}' requires a valid option.",
		self::CHOOSE_MIN_OPTS		=> "Field '{0}' requires at least {1} chosen option(s) at minimal.",
		self::CHOOSE_MAX_OPTS		=> "Field '{0}' requires {1} of the selected option(s) at maximum.",
		self::CHOOSE_MIN_OPTS_BUBBLE=> "Please select at least {0} options as minimal.",
		self::CHOOSE_MAX_OPTS_BUBBLE=> "Please select up to {0} options at maximum.",
	);

	const RESULT_ERRORS		= 0;
	const RESULT_SUCCESS	= 1;
	const RESULT_NEXT_PAGE	= 2;
	
	const FIELD_RENDER_MODE_NORMAL			= 'normal';
	const FIELD_RENDER_MODE_NO_LABEL		= 'no-label';
	const FIELD_RENDER_MODE_LABEL_AROUND	= 'label-around';
	
	const ERROR_RENDER_MODE_ALL_TOGETHER		= 'all-together';
	const ERROR_RENDER_MODE_BEFORE_EACH_CONTROL	= 'before-each-control';
	const ERROR_RENDER_MODE_AFTER_EACH_CONTROL	= 'after-each-control';
	
	/**
	 * @var MvcCore_Controller
	 */
	public $Controller = null;
	
	/**
	 * @var SimpleForm_Core_View
	 */
	public $View = null;

	public $Id = '';
	public $Method = self::METHOD_POST;
	public $Enctype = self::ENCTYPE_URLENCODED;
	public $Action = '';
	public $Lang = '';
	public $CssClass = '';
	public $Attributes = array();
	public $SuccessUrl = '';
	public $NextStepUrl = '';
	public $ErrorUrl = '';
	public $Result = self::RESULT_SUCCESS;
	public $Translator = null;
	public $Translate = null;
	public $Required = null;
	public $Fields = array();
	public $Data = array();
	public $Errors = array();

	public $FieldsDefaultRenderMode = self::FIELD_RENDER_MODE_NORMAL;
	public $ErrorsRenderMode = SimpleForm::ERROR_RENDER_MODE_ALL_TOGETHER;
	public $TemplatePath = '';
	public $TemplateTypePath = 'Scripts';

	public $Js = array();
	public $Css = array();
	public $JsBaseFile = '__SIMPLE_FORM_DIR__/simple-form.js';
	public $JsRenderer = null;
	public $CssRenderer = null;

	protected $initialized = 0;
	protected static $js = array();
	protected static $css = array();
	protected static $jsAssetsRootDir = '';
	protected static $cssAssetsRootDir = '';

	public function RenderJs () {
		if (!$this->Js) return '';
		$jsFiles = $this->completeAssets('js');
		$jsFilesContent = '';
		$fieldsConstructors = array();
		$loadJsFilesContents = !is_callable($this->JsRenderer);
		if (!isset(self::$js[$this->JsBaseFile])) {
			$this->JsBaseFile = $this->absolutizeAssetPath($this->JsBaseFile, 'js');
			self::$js[$this->JsBaseFile] = TRUE;
			$this->renderAssetFile($jsFilesContent, $this->JsRenderer, $loadJsFilesContents, $this->JsBaseFile);
		}
		foreach ($jsFiles as $jsFile) {
			$this->renderAssetFile($jsFilesContent, $this->JsRenderer, $loadJsFilesContents, $jsFile);
		}
		foreach ($this->Js as $item) {
			$paramsStr = json_encode($item[2]);
			$paramsStr = mb_substr($paramsStr, 1, mb_strlen($paramsStr) - 2);
			$fieldsConstructors[] = "new " . $item[1] . "(" . $paramsStr . ")";
		}
		$result = $jsFilesContent."new SimpleForm("
			."document.getElementById('".$this->Id."'),"
			."[".implode(',', $fieldsConstructors)."]"
		.")";
		if (class_exists('MvcCore_View') && property_exists('MvcCore_View', 'Doctype') && strpos(MvcCore_View::$Doctype, 'XHTML') !== FALSE) {
			$result = '/* <![CDATA[ */' . $result . '/* ]]> */';
		}
		return '<script type="text/javascript">' . $result . '</script>';
	}
	public function RenderCss () {
		if (!$this->Css) return '';
		$cssFiles = $this->completeAssets('css');
		$cssFilesContent = '';
		$loadCssFilesContents = !is_callable($this->CssRenderer);
		foreach ($cssFiles as $cssFile) {
			$this->renderAssetFile($cssFilesContent, $this->CssRenderer, $loadCssFilesContents, $cssFile);
		}
		if (!$loadCssFilesContents) return '';
		return '<style type="text/css">'.$cssFilesContent.'</style>';
	}
	public function SetUpCsrf () {
		$requestPath = $this->getRequestPath();
		$randomHash = bin2hex(openssl_random_pseudo_bytes(32));
		$nowTime = (string)time();
		$name = '____'.sha1($this->Id . $requestPath . 'name' . $nowTime . $randomHash);
		$value = sha1($this->Id . $requestPath . 'value' . $nowTime . $randomHash);
		SimpleForm_Core_Helpers::SetSessionCsrf($this->Id, array($name, $value));
		return array($name, $value);
	}
	public function GetCsrf () {
		list($name, $value) = SimpleForm_Core_Helpers::GetSessionCsrf($this->Id);
		return (object) array('name' => $name, 'value' => $value);
	}
	protected function getRequestPath () {
		$requestUri = $_SERVER['REQUEST_URI'];
		$lastQuestionMark = mb_strpos($requestUri, '?');
		if ($lastQuestionMark !== FALSE) $requestUri = mb_substr($requestUri, 0, $lastQuestionMark);
		$protocol = (isset($_SERVER['HTTPS']) && strtolower($_SERVER['HTTPS']) == 'on') ? 'https:' : 'http:';
		return $protocol . '//' . $_SERVER['HTTP_HOST'] . $requestUri;
	}
	protected function checkCsrf ($rawRequestParams = array()) {
		$result = FALSE;
		$sessionCsrf = SimpleForm_Core_Helpers::GetSessionCsrf($this->Id);
		list($name, $value) = $sessionCsrf ? $sessionCsrf : array(NULL, NULL);
		if (!is_null($name) && !is_null($value)) {
			if (isset($rawRequestParams[$name]) && $rawRequestParams[$name] === $value) {
				$result = TRUE;
			}
		}
		if (!$result) {
			$errorMsg = SimpleForm::$DefaultMessages[self::CSRF];
			if ($this->Translate) {
				$translator = $this->Translator;
				$errorMsg = $translator($errorMsg);
			}
			$this->AddError($errorMsg);
		}
	}
	protected function completeAssets ($assetsKey = '') {
		$files = array();
		$assetsKeyUcFirst = ucfirst($assetsKey);
		foreach ($this->$assetsKeyUcFirst as $item) {
			$files[$this->absolutizeAssetPath($item[0], $assetsKey)] = TRUE;
		}
		$files = array_keys($files);
		foreach ($files as $key => $file) {
			if (isset(self::${$assetsKey}[$file])) {
				unset($files[$key]);
			} else {
				self::${$assetsKey}[$file] = TRUE;
			}
		}
		return array_values($files);
	}
	protected function absolutizeAssetPath ($path = '', $assetsKey = '') {
		$assetsRootDir = $assetsKey == 'js' ? static::$jsAssetsRootDir : static::$cssAssetsRootDir;
		return str_replace(
			array('__SIMPLE_FORM_DIR__', '\\'),
			array($assetsRootDir, '/'),
			$path
		);
	}
	protected function renderAssetFile (& $content, & $renderer, $loadContent, $absPath) {
		if ($loadContent) {
			$content .= trim(file_get_contents($absPath), "\n\r;") . ';';
		} else {
			$renderer(new SplFileInfo($absPath));
		}
	}
	protected function submitFields ($rawRequestParams = array()) {
		foreach ($this->Fields as $fieldName => $field) {
			if ($field->Readonly) {
				$safeValue = $field->GetValue(); // get by SetDefaults(array()) call
			} else {
				$safeValue = $this->submitField($fieldName, $rawRequestParams, $field);
			}
			if (is_null($safeValue)) $safeValue = '';
			$field->SetValue($safeValue);
			if (!($field instanceof SimpleForm_Button)) {
				$this->Data[$fieldName] = $safeValue;
			}
		}
		//xcv($rawRequestParams);
		//yxcv($this->Data);
		SimpleForm_Core_Helpers::SetSessionErrors($this->Id, $this->Errors);
		SimpleForm_Core_Helpers::SetSessionData($this->Id, $this->Data);
	}
	protected function submitField ($fieldName, & $rawRequestParams, SimpleForm_Core_Field & $field) {
		$result = null;
		if (!$field->Validators) {
			$submitValue = isset($rawRequestParams[$fieldName]) ? $rawRequestParams[$fieldName] : $field->GetValue();
			$result = $submitValue;
		} else {
			//xcv($field->Validators);
			foreach ($field->Validators as $validatorKey => $validator) {
				if ($validatorKey > 0) {
					$submitValue = $result; // take previous
				} else {
					// take submitted or default by SetDefault(array()) call in first verification loop
					$submitValue = isset($rawRequestParams[$fieldName]) ? $rawRequestParams[$fieldName] : $field->GetValue();
				}
				if ($validator instanceof Closure) {
					$safeValue = $validator(
						$submitValue, $fieldName, $field, $this
					);
				} else /*if (gettype($validator) == 'string')*/ {
					$validatorInstance = SimpleForm_Core_Validator::Create($validator, $this);
					$safeValue = $validatorInstance->Validate(
						$submitValue, $fieldName, $field
					);
				}
				if (is_null($safeValue)) $safeValue = '';
				if (
					(
						(gettype($safeValue) == 'string' && strlen($safeValue) === 0) || 
						(gettype($safeValue) == 'array' && count($safeValue) === 0)
					) && $field->Required
				) {
					$errorMsg = SimpleForm::$DefaultMessages[SimpleForm::REQUIRED];
					if ($this->Translate) {
						$translator = $this->Translator;
						$errorMsg = $translator($errorMsg);
					}
					$errorMsg = SimpleForm_Core_View::Format(
						$errorMsg, array($field->Label ? $field->Label : $fieldName)
					);
					$this->AddError(
						$fieldName, $errorMsg
					);
				}
				$result = $safeValue;
			}
		}
		return $result;
	}
}
