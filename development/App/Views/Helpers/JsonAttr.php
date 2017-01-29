<?php

namespace App\Views\Helpers;

class JsonAttr
{
	/**
	 * Convert any php value to json format, which is available to use in html attribute
	 * @param $object mixed
	 * @return string
	 */
	public function JsonAttr ($object = NULL) {
		return rawurlencode(\MvcCore\Tool::EncodeJson($object));
	}
}