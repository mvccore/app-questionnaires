<?php

require_once('/../SimpleForm.php');
require_once('/Core/Field.php');
require_once('/Core/Exception.php');
require_once('/Core/View.php');

class SimpleForm_ResetInput extends SimpleForm_Core_Field
{
	public $Type = 'reset';
	public $Value = 'Reset';
	public $RenderMode = SimpleForm::FIELD_RENDER_MODE_NO_LABEL;
	public $Accesskey = null;
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
	public function RenderControl () {
		$attrsStr = $this->renderControlAttrsWithFieldVars(
			array('Accesskey',)
		);
		return SimpleForm_Core_View::Format(static::$templates->control, array(
			'id'		=> $this->Id, 
			'name'		=> $this->Name, 
			'type'		=> $this->Type,
			'value'		=> $this->Value,
			'attrs'		=> $attrsStr ? " $attrsStr" : '', 
		));
	}
}