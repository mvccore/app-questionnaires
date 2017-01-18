<?php

class App_Controllers_System extends App_Controllers_Base
{
	public function JsErrorsLogAction () {
		$this->DisableView();
		if (MvcCore_Config::IsProduction()) return;
		$keys = array(
			'message'=>1,
			'uri'		=> 1,
			'file'		=> 1,
			'line'		=> 0,
			'column'	=> 0,
			'callstack'	=> 1,
			'browser'	=> 1,
			'platform'	=> 0,
		);
		$data = array();
		foreach ($keys as $key => $hex) {
			$param = $this->GetParam($key);
			if ($hex) $param = self::_hexToStr($param);
			$param = preg_replace("#[^a-zA-Z0-9/\&\(\)\[\]\.\'\"%#\$]#", "", $param);
			$data[$key] = $param;
		}
		$msg = json_encode($data);
		MvcCore_Debug::Log($msg, MvcCore_Debug::JAVASCRIPT);
	}
	private static function _hexToStr ($hex) {
		$string='';
		for ($i = 0; $i < strlen($hex) - 1; $i += 2){
			$string .= chr(hexdec($hex[$i].$hex[$i+1]));
		}
		return $string;
	}
}