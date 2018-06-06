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
	 * @param mixed $args,...  any arguments converted to string 
	 * @return string
	 */
	public function Format ($template = ''/*, ...$args*/) {
		$args = func_get_args();
		if (count($args) == 0) return '';
		if (count($args) == 1) return $args[0];
		$str = array_shift($args);
		foreach ($args as $key => $value) {
			$pos = strpos($str, '{'.$key.'}');
			$str = substr($str, 0, $pos) . $value . substr($str, $pos + strlen($key) + 2);
		}
		return $str;
	}
}
