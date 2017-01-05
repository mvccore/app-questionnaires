<?php

class App_Views_Helpers_Translate
{
	private $_controller;
	public function __construct (MvcCore_View & $view) {
		$this->_controller = $view->Controller;
	}
	public function Translate ($key = '', $lang = '') {
		return $this->_controller->Translate($key, $lang);
	}
}