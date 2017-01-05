<?php

class App_Views_Helpers_Format
{
	public function Format () {
		$args = func_get_args();
		if (count($args) == 0) return '';
		if (count($args) == 1) return $args[0];
		$str = array_shift($args);
		foreach ($args as $key => $val) {
			$str = str_replace('{'.$key.'}', (string)$val, $str);
		}
		return $str;
	}
}