<?php

require_once('/Core/Field.php');

class SimpleForm_Hidden extends SimpleForm_Core_Field
{
	public $Type = 'hidden';
	public $Validators = array('SafeString');
}
