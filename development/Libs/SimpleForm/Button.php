<?php

require_once('/../SimpleForm.php');
require_once('/Core/Field.php');
require_once('/Core/View.php');

class SimpleForm_Button extends SimpleForm_Core_Field
{
	public $Type = 'button'; // submit | reset | button
	public $Value = 'OK';
	public $RenderMode = SimpleForm::FIELD_RENDER_MODE_NO_LABEL;
	public $Accesskey = null;
	protected static $templates = array(
		'control'	=> '<button id="{id}" name="{name}" type="{type}"{attrs}>{value}</button>',
	);
	public function __construct(array $cfg = array()) {
		parent::__construct($cfg);
		static::$templates = (object) array_merge((array)parent::$templates, (array)self::$templates);
	}
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
