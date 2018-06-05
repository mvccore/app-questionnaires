<?php

namespace App\Views\Helpers;

class Translate extends \MvcCore\Ext\Views\Helpers\AbstractHelper
{
	protected static $instance = NULL;

	private $_translator;

	public function __construct () {
		$this->_translator = \App\Models\Translator::GetInstance();
	}

	public function Translate ($key = '', $lang = '') {
		return $this->_translator->Translate($key, $lang);
	}
}
