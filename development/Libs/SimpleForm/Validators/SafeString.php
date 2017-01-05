<?php

require_once('/../../SimpleForm.php');
require_once('/../Core/Validator.php');
require_once('/../Core/Field.php');
require_once('/../Core/View.php');

class SimpleForm_Validators_SafeString extends SimpleForm_Core_Validator
{
	public function Validate ($submitValue, $fieldName, SimpleForm_Core_Field & $field) {
		$submitValue = trim($submitValue);
		// do not allow characters:   \'"#`?!=[]{}<>
		// $safeValue = preg_replace("#[\\'\"\#`\?\!\=\[\]\{\}\<\>]#", '', trim($submitValue));
		// do not allow characters:   \'"#`<>
		$safeValue = preg_replace("#[\\'\"\#`\<\>]#", '', $submitValue);
		if (mb_strlen($safeValue) !== mb_strlen($submitValue)) {
			$errorMsg = SimpleForm::$DefaultMessages[SimpleForm::INVALID_CHARS];
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
