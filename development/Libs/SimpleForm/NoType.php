<?php

require_once('/Core/Field.php');

class SimpleForm_NoType extends SimpleForm_Core_Field
{
	public $Type = '';
	public $Validators = array('SafeString');
}
