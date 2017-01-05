<?php

require_once('/../../SimpleForm.php');
require_once('/../Core/Field.php');
require_once('ValueInOptions.php');

class SimpleForm_Validators_MaxSelectedOptions extends SimpleForm_Validators_ValueInOptions
{
	public function Validate ($submitValue, $fieldName, SimpleForm_Core_Field & $field) {
		$safeValue = is_array($submitValue) ? $submitValue : array();
		$safeValueCount = count($safeValue);
		// check if there is enough options checked
		if ($field->MaxSelectedOptionsCount > 0 && $safeValueCount > $field->MaxSelectedOptionsCount) {
			$this->addErrorMsg(
				SimpleForm::$DefaultMessages[SimpleForm::CHOOSE_MAX_OPTS], $fieldName, $field, array($field->MaxSelectedOptionsCount)
			);
		}
		return $safeValue;
	}
}
