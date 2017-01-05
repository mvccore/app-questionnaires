<?php

require_once('/Text.php');

class SimpleForm_Email extends SimpleForm_Text
{
	public $Type = 'email';
	public $Validators = array('SafeString', 'Maxlength', 'Pattern', 'Email');
}
