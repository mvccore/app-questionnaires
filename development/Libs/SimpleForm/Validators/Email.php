<?php

require_once('/../../SimpleForm.php');
require_once('/../Core/Validator.php');
require_once('/../Core/Field.php');
require_once('/../Core/View.php');

class SimpleForm_Validators_Email extends SimpleForm_Core_Validator
{
	public function Validate ($submitValue, $fieldName, SimpleForm_Core_Field & $field) {
		$submitValue = trim($submitValue);
		$safeValue = preg_replace("#[\\'\"\#`\?\!\=\[\]\{\}\(\)\<\>]#", '', $submitValue);
		$validEmail = $this->checkTheRightEmailForm($safeValue);
		if ((mb_strlen($safeValue) > 0 && !$validEmail) || (!$validEmail && $field->Required)) {
			$errorMsg = SimpleForm::$DefaultMessages[SimpleForm::EMAIL];
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
	protected function checkTheRightEmailForm ($value) {
		$atom = "[-a-z0-9!#$%&'*+/=?^_`{|}~]"; // RFC 5322 unquoted characters in local-part
		$localPart = "(\"([ !\\x23-\\x5B\\x5D-\\x7E]*|\\\\[ -~])+\"|$atom+(\\.$atom+)*)"; // quoted or unquoted
		$chars = "a-z0-9\x80-\xFF"; // superset of IDN
		$domain = "[$chars]([-$chars]{0,61}[$chars])"; // RFC 1034 one domain component
		return (bool) preg_match("(^$localPart@($domain?\\.)+[-$chars]{2,19}\\z)i", $value);
	}
}
