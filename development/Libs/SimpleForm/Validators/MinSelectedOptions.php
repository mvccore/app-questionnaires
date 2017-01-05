<?php

require_once('/../../SimpleForm.php');
require_once('/../Core/Field.php');
require_once('ValueInOptions.php');

class SimpleForm_Validators_MinSelectedOptions extends SimpleForm_Validators_ValueInOptions
{
	public function Validate ($submitValue, $fieldName, SimpleForm_Core_Field & $field) {
		$safeValue = is_array($submitValue) ? $submitValue : array();
		$safeValueCount = count($safeValue);
		// check if there is enough options checked
		if ($field->MinSelectedOptionsCount > 0 && $safeValueCount < $field->MinSelectedOptionsCount) {
			$this->addErrorMsg(
				SimpleForm::$DefaultMessages[SimpleForm::CHOOSE_MIN_OPTS], $fieldName, $field, array($field->MinSelectedOptionsCount)
			);
		}
		return $safeValue;
	}
}
