<?php

namespace App\Forms\CustomValidators;

class Connections extends \MvcCore\Ext\Forms\Validators\ValueInOptions
{
	public function Validate ($rawSubmittedValue) {
		$valid = TRUE;
		// filter submitted values for duplicated connections
		$safeValue = [];
		$valueKeys = [];
		$keysToUnset = [];
		$rawSubmittedValue = (array) $rawSubmittedValue;
		foreach ($rawSubmittedValue as $key => $submitValue) {
			$submitValue = preg_replace("#[^0-9]#", '', trim($submitValue));
			$submitValueInt = intval($submitValue);
			if (strlen($submitValue) > 0 && !isset($valueKeys[$submitValueInt])) {
				$valueKeys[$submitValueInt] = $key;
			} else if (strlen($submitValue) > 0) {
				$keysToUnset[] = $submitValueInt;
			}
		}
		foreach ($keysToUnset as $keyToUnset) {
			unset($valueKeys[$keyToUnset]);
			$valid = FALSE;
		}
		foreach ($valueKeys as $submitValueInt => $key) 
			$safeValue[intval($key)] = $submitValueInt;
		if (
			!$valid || (
				$this->field->GetRequired() && count($this->field->GetOptions()) !== count($safeValue)
			)
		) $this->field->AddValidationError(static::GetErrorMessage(static::ERROR_VALID_OPTION));
		return $safeValue;
	}
}
