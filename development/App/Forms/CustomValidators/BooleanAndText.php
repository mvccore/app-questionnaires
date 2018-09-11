<?php

namespace App\Forms\CustomValidators;

class BooleanAndText extends \MvcCore\Ext\Forms\Validators\ValueInOptions
{
	/**
	 * 
	 * @param string|array $rawSubmittedValue Raw submitted value from user.
	 * @return array Safe submitted value or `NULL` if not possible to return safe value.
	 */
	public function Validate ($rawSubmittedValue) {
		// possible values - empty array, array with one or two elements,
		//   first element could be boolean and also a text field
		$valid = TRUE;
		$result = [];
		$rawSubmittedValue = (array) $rawSubmittedValue;
		foreach ($rawSubmittedValue as $key => $submitValue) {
			if ($key > 1) break;
			if ($key == 0 && count($rawSubmittedValue) > 1) {
				$safeValueLocal = strtolower(trim($submitValue));
				$safeValueLocal = in_array($safeValueLocal, ['yes', 'no']) ? $safeValueLocal : '';
				if ($this->field->GetRequired() && !$safeValueLocal) $valid = FALSE;
				if ($safeValueLocal) $result[] = $safeValueLocal;
			} else {
				$safeValueLocal = preg_replace("#[\\'\"\#`\<\>\[\]]#", '', trim($submitValue));
				$result[] = mb_substr($safeValueLocal, 0, 255);
			}
		}
		if (!$valid) 
			$this->field->AddValidationError(static::GetErrorMessage(static::ERROR_VALID_OPTION));
		return $result;
	}
}
