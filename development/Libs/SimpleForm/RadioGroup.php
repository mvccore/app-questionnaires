<?php

require_once('/Core/FieldGroup.php');

class SimpleForm_RadioGroup extends SimpleForm_Core_FieldGroup
{
	public $Type = 'radio';
	public $Value = '';
	public $Validators = array('ValueInOptions');
	public function __construct(array $cfg = array()) {
		parent::__construct($cfg);
		static::$templates = (object) array_merge((array)parent::$templates, (array)self::$templates);
	}
}