<?php

class App_Views_Helpers_JsonAttr
{
	public function __construct () {
		// from PHP 5.4
		if (!defined('JSON_UNESCAPED_SLASHES')) define('JSON_UNESCAPED_SLASHES', 64);
		if (!defined('JSON_UNESCAPED_UNICODE')) define('JSON_UNESCAPED_UNICODE', 256);	
	}
	/**
	 * Convert any php value to json format, which is available to use in html attribute
	 * @param $object mixed
	 */
	public function JsonAttr ($object = NULL) {
		return rawurlencode(
			json_encode($object, JSON_HEX_TAG | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP)
		);
	}
}