<?php

require_once('SimpleForm/Core/Base.php');
require_once('SimpleForm/Core/Exception.php');
require_once('SimpleForm/Core/Field.php');
require_once('SimpleForm/Core/Helpers.php');
require_once('SimpleForm/Core/View.php');

class SimpleForm extends SimpleForm_Core_Base
{
	/* getters & setters *********************************************************************/
	public function SetId ($id = '') {
		$this->Id = $id;
		return $this;
	}
	public function SetAction ($url = '') {
		$this->Action = $url;
		return $this;
	}
    public function SetMethod ($method = '') {
		$this->Method = $method;
		return $this;
	}
	public function SetEnctype ($enctype = '') {
		$this->Enctype = $enctype;
		return $this;
	}
	public function SetLang ($lang = '') {
		$this->Lang = $lang;
		return $this;
	}
	public function SetCssClass ($cssClass = '') {
		$this->CssClass = $cssClass;
		return $this;
	}
	public function AddCssClass ($cssClass = '') {
		$this->CssClass .= (($this->CssClass) ? ' ' : '') . $cssClass;
		return $this;
	}
	public function SetAttributes (array $attributes = array()) {
		$this->Attributes = $attributes;
		return $this;
	}
	public function SetTranslator ($handler = null) {
		$this->Translator = $handler;
		return $this;
	}
	public function SetTranslate ($translate = TRUE) {
		$this->Translate = $translate;
		return $this;
	}
	public function SetRequired ($required = TRUE) {
		$this->Required = $required;
		return $this;
	}
	public function SetSuccessUrl ($url = '') {
		$this->SuccessUrl = $url;
		return $this;
	}
	public function SetNextStepUrl ($url = '') {
		$this->NextStepUrl = $url;
		return $this;
	}
	public function SetErrorUrl ($url = '') {
		$this->ErrorUrl = $url;
		return $this;
	}
	public function SetTemplatePath ($path = '') {
		$this->TemplatePath = str_replace('\\', '/', $path);
		return $this;
	}
	public function SetJs (array $jsFilesClassesAndConstructorParams = array()) {
		$this->Js = array();
		foreach ($jsFilesClassesAndConstructorParams as $item) {
			$this->AddJs($item[0], $item[1], $item[2]);
		}
		return $this;
	}
	public function AddJs ($jsFile = '', $jsClass = 'SimpleForm.FieldType', $jsConstructorParams = array()) {
		$this->Js[] = array($jsFile, $jsClass, $jsConstructorParams);
		return $this;
	}
	public function SetCss (array $cssFiles = array()) {
		$this->Css = array();
		foreach ($cssFiles as $item) $this->AddCss($item);
		return $this;
	}
	public function AddCss ($cssFile = '') {
		$this->Css[] = array($cssFile);
		return $this;
	}
	public function SetJsRenderer (callable $jsRenderer) {
		$this->JsRenderer = $jsRenderer;
		return $this;
	}
	public function SetCssRenderer (callable $cssRenderer) {
		$this->CssRenderer = $cssRenderer;
		return $this;
	}
	/* public methods ************************************************************************/
	public function __construct (/*MvcCore_Controller*/ & $controller) {
		$this->Controller = $controller;
		$baseLibPath = str_replace('\\', '/', __DIR__ . '/SimpleForm');
		if (!static::$jsAssetsRootDir) static::$jsAssetsRootDir = $baseLibPath;
		if (!static::$cssAssetsRootDir) static::$cssAssetsRootDir = $baseLibPath;
	}
	public function Init () {
		if ($this->initialized) return $this;
		$this->initialized = 1;
		if (!$this->Id) {
			$clsName = get_class($this);
			throw new SimpleForm_Core_Exception("No form 'Id' property defined in: '$clsName'.");
		}
		if ((is_null($this->Translate) || $this->Translate === TRUE) && !is_null($this->Translator)) {
			$this->Translate = TRUE;
		} else {
			$this->Translate = FALSE;
		}
		return $this;
	}
	public function AddError () {
		$args = func_get_args();
		if (count($args) === 2) {
			$fieldName = $args[0];
			$errorTextUtf8 = iconv(mb_detect_encoding($args[1], mb_detect_order(), true), "UTF-8", $args[1]);
			$errorTextUtf8 = strip_tags($errorTextUtf8);
			$this->Errors[$fieldName] = $errorTextUtf8;
			if (isset($this->Fields[$fieldName])) {
				$this->Fields[$fieldName]->AddError($errorTextUtf8);
			}
		} else if (count($args) === 1) {
			$this->Errors[] = iconv(mb_detect_encoding($args[0], mb_detect_order(), true), "UTF-8", $args[0]);
		}
		$this->Result = SimpleForm::RESULT_ERRORS;
		return $this;
	}
	protected function setUpFields () {
		foreach ($this->Fields as $fieldKey => $field) {
			// translate fields if necessary
			$field->SetUp();
		}
		$errors = SimpleForm_Core_Helpers::GetSessionErrors($this->Id);
		foreach ($errors as $fieldName => $errorMsg) {
			$this->AddError($fieldName, $errorMsg);
			if (isset($this->Fields[$fieldName])) {
				// add error classes into settings config where necessary
				$field = $this->Fields[$fieldName];
				$field->AddCssClass('error');
				if (method_exists($field, 'AddGroupCssClass')) {
					$field->AddGroupCssClass('error');
				}
			}
		}
		$data = SimpleForm_Core_Helpers::GetSessionData($this->Id);
		if ($data) $this->SetDefaults($data);
		$this->initialized = 2;
	}
	public function AddFields () {
		if (!$this->initialized) $this->Init();
		$fields = func_get_args();
		foreach ($fields as $field) {
			$this->AddField($field);
		}
		return $this;
	}
	public function AddField (SimpleForm_Core_Field & $field) {
		if (!$this->initialized) $this->Init();
		$field->OnAdded($this);
		$this->Fields[$field->Name] = $field;
		return $this;
	}
	public function SetDefaults (array $defaults = array()) {
		if (!$this->initialized) $this->Init();
		foreach ($defaults as $fieldName => $fieldValue) {
			if (isset($this->Fields[$fieldName])) {
				$this->Fields[$fieldName]->SetValue($fieldValue);
				if ($fieldValue) $this->Data[$fieldName] = $fieldValue;
			}
		}
	}
	public function Submit ($rawParams = array()) {
		if (!$this->initialized) $this->Init();
		SimpleForm_Core_Helpers::ValidateMaxPostSizeIfNecessary($this);
		if (!$rawParams) $rawParams = $this->Controller->GetRequest()->params;
		$this->checkCsrf($rawParams);
		$this->submitFields($rawParams);
		// yxcv($rawParams);
		return array(
			$this->Result,
			$this->Data,
			$this->Errors,
		);
	}
	public function ClearSession () {
		$this->Data = array();
		SimpleForm_Core_Helpers::SetSessionData($this->Id, array());
		SimpleForm_Core_Helpers::SetSessionErrors($this->Id, array());
	}
	public function __toString () {
		try {
			$result = $this->Render();
		} catch (Exception $e) {
			if (class_exists('Debug')) Debug::_exceptionHandler($e);
			$result = $e->GetMessage();
		}
		return $result;
	}
	// called only when we are creating form like eshop chart, 
	// where every field is filled by database
	// and we need to know form values befre rendering
	public function LoadSession () {
		if (!$this->initialized) $this->Init();
		if ($this->initialized < 2) $this->setUpFields();
	}
	public function Render () {
		$result = '';
		if (!$this->initialized) $this->Init();
		if ($this->initialized < 2) $this->setUpFields();
		$this->View = new SimpleForm_Core_View($this);
		$this->View->SetUp($this);
		if ($this->TemplatePath) {
			$result = $this->View->RenderTemplate();
		} else {
			$result = $this->View->RenderNaturally();
		}
		$this->Errors = array();
		SimpleForm_Core_Helpers::SetSessionErrors($this->Id, array());
		return $result;
	}
	public function RenderFormBegin () {
		if (!$this->initialized) $this->Init();
		return $this->View->RenderFormBegin();
	}
	public function RenderContent () {
		if (!$this->initialized) $this->Init();
		return $this->View->RenderContent();
	}
	public function RenderFormEnd () {
		if (!$this->initialized) $this->Init();
		return $this->View->RenderFormEnd();
	}
	public function RedirectAfterSubmit () {
		if (!$this->initialized) $this->Init();
		SimpleForm_Core_Helpers::SetSessionErrors($this->Id, $this->Errors);
		SimpleForm_Core_Helpers::SetSessionData($this->Id, $this->Data);
		$url = "";
		if ($this->Result === SimpleForm::RESULT_ERRORS) {
			$url = $this->ErrorUrl;
		} else if ($this->Result === SimpleForm::RESULT_SUCCESS) {
			$url = $this->SuccessUrl;
		} else if ($this->Result === SimpleForm::RESULT_NEXT_PAGE) {
			$url = $this->NextStepUrl;
		}
		$ctrl = $this->Controller;
		$ctrl::Redirect($url, 303);
	}
}
