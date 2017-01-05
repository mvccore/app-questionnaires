<?php

require_once('/../../SimpleForm.php');
require_once('/../Core/Validator.php');
require_once('/../Core/Field.php');
require_once('/../Core/View.php');

class SimpleForm_Validators_ZipCode extends SimpleForm_Core_Validator
{
	public function Validate ($submitValue, $fieldName, SimpleForm_Core_Field & $field) {
		$submitValue = trim($submitValue);
		$safeValue = preg_replace("#[^0-9a-zA-Z\-]#", '', $submitValue);
		$formLang = $this->Form->Lang;
		$localizedResult = TRUE;
		if ($formLang) {
			$localizedVerifyMethodName = 'validate_' . strtoupper($formLang);
			if (method_exists($this, $localizedVerifyMethodName)) {
				list($safeValue, $localizedResult) = $this->$localizedVerifyMethodName($safeValue);
			}
		}
		if (mb_strlen($safeValue) !== mb_strlen($submitValue) || !$localizedResult) {
			$errorMsg = SimpleForm::$DefaultMessages[SimpleForm::ZIP_CODE];
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
	protected function validate_CS ($safeValue) {
		$success = strlen($safeValue) == 5;
		return array($safeValue, $success);
	}
	protected function validate_SK ($safeValue) {
		$success = strlen($safeValue) == 5;
		return array($safeValue, $success);
	}
}
