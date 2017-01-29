<?php

namespace App\Views\Helpers;

class Translate
{
	private $_translator;
	public function __construct (\MvcCore\View & $view) {
		$this->_translator = \App\Models\Translator::GetInstance();
	}
	public function Translate ($key = '', $lang = '') {
		return $this->_translator->Translate($key, $lang);
	}
}