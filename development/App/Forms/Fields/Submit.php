<?php

class App_Forms_Fields_Submit extends SimpleForm_SubmitButton
{
	public function __construct(array $cfg = array()) {
		parent::__construct($cfg);
		static::$templates->control = '<button id="{id}" name="{name}" type="{type}"{attrs}><span><b>{value}</b></span></button>';
	}
}
