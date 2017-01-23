<?php

class App_Views_Helpers_JsonAttr
{
	/**
	 * Convert any php value to json format, which is available to use in html attribute
	 * @param $object mixed
	 */
	public function JsonAttr ($object = NULL) {
		return rawurlencode(
			MvcCore_Tool::EncodeJson($object)
		);
	}
}