<?php

require_once('/../SimpleForm.php');
require_once('/Button.php');
require_once('/Core/Exception.php');

class SimpleForm_ResetButton extends SimpleForm_Button
{
	public $Type = 'reset';
	public $Value = 'Reset';
	public $Validators = array();
	public $JsClass = 'SimpleForm.Reset';
	public $Js = '__SIMPLE_FORM_DIR__/fields/reset.js';
	
	public function SetAccesskey ($accesskey) {
		$this->Accesskey = $accesskey;
		return $this;
	}

	public function OnAdded (SimpleForm & $form) {
		parent::OnAdded($form);
		if (!$this->Value) {
			$clsName = get_class($this);
			throw new SimpleForm_Core_Exception("No 'Value' defined for form field: '$clsName'.");
		}
	}
	
	public function SetUp () {
		parent::SetUp();
		$this->Form->AddJs($this->Js, $this->JsClass, array($this->Name));
		if ($this->Translate && $this->Value) {
			$translator = $this->Form->Translator;
			$this->Value = $translator($this->Value, $this->Form->Lang);
		}
	}
}
