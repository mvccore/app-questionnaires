<?php

namespace App\Models;

class Base extends \MvcCore\Model
{
	const TABLE_ANSWERS = 'Answers';
	const TABLE_EXECUTED = 'Executed';
	const TABLE_PERSONS = 'Persons';

	public static function TranslateHtmlEntitiesToUtfChars ($str = '') {
		$result = $str;
		$matches = array();
		preg_match_all("/&#([0-9]*);/", $str, $matches, PREG_OFFSET_CAPTURE);
		if (isset($matches[0]) && count($matches[0]) > 0) {
			$values = array(substr($str, 0, $matches[0][0][1]));
			for ($i = 0, $l = count($matches[0]); $i < $l; $i += 1) {
				$outerMatchPos = $matches[0][$i][1];
				$outerMatchLen = strlen($matches[0][$i][0]);
				$entityIndex = intval($matches[1][$i][0]);
				$entityValue = chr($entityIndex);
				$values[] = $entityValue;
				if (isset($matches[0][$i + 1])) {
					$nextMatchPos = $matches[0][$i + 1][1];
					$values[] = substr(
						$str,
						$outerMatchPos + $outerMatchLen,
						$nextMatchPos - ($outerMatchPos + $outerMatchLen)
					);
				} else {
					$values[] = substr($str, $outerMatchPos + $outerMatchLen);
				}
			}
			$result = implode('', $values);
		}
		return $result;
	}
}