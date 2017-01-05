<?php

require_once('/../../SimpleForm.php');
require_once('/../Core/Validator.php');
require_once('/../Core/Field.php');
require_once('/../Core/View.php');

class SimpleForm_Validators_Pattern extends SimpleForm_Core_Validator
{
	public function Validate ($submitValue, $fieldName, SimpleForm_Core_Field & $field) {
		$safeValue = '';
		$submitValue = trim($submitValue);
		if (isset($field->Pattern) && !is_null($field->Pattern)) {
			$pattern = $field->Pattern;
			if (mb_strpos($pattern, "#") !== 0) {
				$pattern = "#" . $pattern . "#";
			}
			preg_match($pattern, $submitValue, $matches);
			if ($matches) {
				$safeValue = $submitValue;
			}
		} else {
			$safeValue = $submitValue;
		}
		if (mb_strlen($safeValue) !== mb_strlen($submitValue)) {
			$errorMsg = SimpleForm::$DefaultMessages[SimpleForm::INVALID_FORMAT];
			if ($this->Translate) {
				$translator = $this->Translator;
				$errorMsg = $translator($errorMsg);
				$label = $field->Label ? $translator($field->Label) : $fieldName;
			} else {
				$label = $field->Label ? $field->Label : $fieldName;
			}
			$errorMsg = SimpleForm_Core_View::Format(
				$errorMsg, array($label, $field->Pattern)
			);
			$this->Form->AddError(
				$fieldName, $errorMsg
			);
		}
		return $safeValue;
	}
}
