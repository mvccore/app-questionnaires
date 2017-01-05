<?php

require_once('/../Core/Validator.php');
require_once('/../Core/Field.php');

class SimpleForm_Validators_RangeField extends SimpleForm_Core_Validator
{
	public function Validate ($submitValue, $fieldName, SimpleForm_Core_Field & $field) {
		$validatorInstance = SimpleForm_Core_Validator::Create('NumberField', $field->Form);
		if ($field->Multiple) {
			$submitValues = is_array($submitValue) ? $submitValue : explode(',',$submitValue);
			$result = array();
			foreach ($submitValues as $item) {
				$result[] = $validatorInstance->Validate(
					$item, $fieldName, $field
				);
			}
			return $result;
		} else {
			return $validatorInstance->Validate(
				$submitValue, $fieldName, $field
			);
		}
	}
}
