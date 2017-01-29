<?php

namespace App\Views\Helpers;

class Format
{
	/**
	 * String formater
	 * Format any string template with curly bracket 
	 * replacements: '...{0}...{1}...', given as first param
	 * with params given as all next arguments
	 * @param string $template string template with curly brackets replacements: '...{0}...{1}...'
	 * @param mixed  $args..,  any arguments converted to string 
	 * @return string
	 */
	public function Format ($template, $args) {
		$arguments = func_get_args();
		if (count($arguments) == 0) return '';
		if (count($arguments) == 1) return $arguments[0];
		$str = array_shift($arguments);
		foreach ($arguments as $key => $val) {
			$str = str_replace('{'.$key.'}', (string)$val, $str);
		}
		return $str;
	}
}