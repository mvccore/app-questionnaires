<?php

require_once('/../../SimpleForm.php');
require_once('/../Core/Validator.php');
require_once('/../Core/Field.php');
require_once('/../Core/Exception.php');
require_once('/../Core/View.php');

class SimpleForm_Validators_CompanyIds extends SimpleForm_Core_Validator
{
	protected static $exceptionMessage = '';
	protected static $errorMessageKey = '';
	public function Validate ($submitValue, $fieldName, SimpleForm_Core_Field & $field) {
		$submitValue = trim($submitValue);
		$safeValue = preg_replace("#[^A-Z0-9]#", "", strtoupper($submitValue));
		$formLang = $this->Form->Lang;
		$validCompanyId = TRUE;
		if ($formLang && strlen($safeValue) > 0) {
			$localizedVerifyMethodName = 'validate_' . strtoupper($formLang);
			if (method_exists($this, $localizedVerifyMethodName)) {
				$validCompanyId = $this->$localizedVerifyMethodName($safeValue);
			} else {
				throw new SimpleForm_Core_Exception(str_replace('{lang}', $formLang, static::$exceptionMessage));
			}
		}
		if (
			(strlen($safeValue) > 0 && !$validCompanyId) || 
			(!$validCompanyId && $field->Required) || 
			strlen($safeValue) !== strlen($submitValue)
		) {
			$errorMsg = SimpleForm::$DefaultMessages[static::$errorMessageKey];
			if ($this->Translate) {
				$translator = $this->Translator;
				$errorMsg = $translator($errorMsg);
				$label = $field->Label ? $translator($field->Label) : $fieldName;
			} else {
				$label = $field->Label ? $field->Label : $fieldName;
			}
			$errorMsg = SimpleForm_Core_View::Format(
				$errorMsg, array($label)
			);
			$this->Form->AddError(
				$fieldName, $errorMsg
			);
		}
		return $safeValue;
	}
}
