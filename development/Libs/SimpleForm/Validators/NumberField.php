<?php

require_once('/../../SimpleForm.php');
require_once('/../Core/Validator.php');
require_once('/../Core/Field.php');
require_once('/../Core/View.php');

class SimpleForm_Validators_NumberField extends SimpleForm_Core_Validator
{
	public function Validate ($submitValue, $fieldName, SimpleForm_Core_Field & $field) {
		$submitValue = trim($submitValue);
		$intValueStr = preg_replace("#[^0-9]#", '', $submitValue);
		$floatValueStr = preg_replace("#[^0-9\.]#", '', str_replace(',','.',$submitValue));
		$errorMsgKeyCommon = '';
		$errorMsgKey = '';
		if (strlen($intValueStr) === 0) {
			if ($field->Required) $errorMsgKey = SimpleForm::NUMBER;
			$safeValue = '';
		} else {
			if ($floatValueStr === $intValueStr) {
				$safeValue = intval($intValueStr);
				$errorMsgKeyCommon = SimpleForm::INTEGER;
			} else {
				$safeValue = floatval($intValueStr);
				$errorMsgKeyCommon = SimpleForm::FLOAT;
			}
			$errorMsgKey = '';
			if (isset($this->Min) && !is_null($field->Min)) {
				if ($safeValue < $field->Min) {
					$errorMsgKey = !is_null($this->Max) ? SimpleForm::RANGE : SimpleForm::GREATER;
				}
			}
			if (isset($this->Max) && !is_null($this->Max)) {
				if ($safeValue > $field->Max) {
					$errorMsgKey = !is_null($this->Min) ? SimpleForm::RANGE : SimpleForm::LOWER;
				}
			}
			if (isset($this->Pattern) && !is_null($this->Pattern)) {
				preg_match("#^".$this->Pattern."$#", (string)$safeValue, $matches);
				if (!$matches) {
					$errorMsgKey = $errorMsgKeyCommon;
				}
			}
		}
		if (mb_strlen($safeValue) !== mb_strlen($submitValue) || $errorMsgKey) {
			$errorMsgKey = $errorMsgKey ? $errorMsgKey : $errorMsgKeyCommon ;
			$errorMsg = SimpleForm::$DefaultMessages[$errorMsgKey];
			if ($this->Translate) {
				$translator = $this->Translator;
				$errorMsg = $translator($errorMsg);
				$label = $field->Label ? $translator($field->Label) : $fieldName;
			} else {
				$label = $field->Label ? $field->Label : $fieldName;
			}
			$errorReplacements = array($label);
			if ($errorMsgKey == SimpleForm::RANGE) {
				$errorReplacements[] = $field->Min;
				$errorReplacements[] = $field->Max;
			} else if ($errorMsgKey == SimpleForm::GREATER) {
				$errorReplacements[] = $field->Min;
			} else if ($errorMsgKey == SimpleForm::LOWER) {
				$errorReplacements[] = $field->Max;
			}
			$errorMsg = SimpleForm_Core_View::Format(
				$errorMsg, $errorReplacements
			);
			$this->Form->AddError(
				$fieldName, $errorMsg
			);
		}
		return $safeValue;
	}
}
