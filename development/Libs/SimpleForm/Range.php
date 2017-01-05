<?php

require_once('/Core/Field.php');
require_once('/Core/View.php');

class SimpleForm_Range extends SimpleForm_Core_Field
{
	public $Type = 'range';
	public $Min = null;
	public $Max = null;
	public $Step = null;
	public $Multiple = FALSE;
	public $Wrapper = '{control}';
	public $Validators = array('RangeField');
	public $JsClass = 'SimpleForm.Range';
	public $Js = '__SIMPLE_FORM_DIR__/fields/range.js';
	public $Css = '__SIMPLE_FORM_DIR__/fields/range.css';

	public function SetMin ($min) {
		$this->Min = $min;
		return $this;
	}
	public function SetMax ($max) {
		$this->Max = $max;
		return $this;
	}
	public function SetStep ($step) {
		$this->Step = $step;
		return $this;
	}
	public function SetMultiple ($multiple) {
		$this->Multiple = $multiple;
		return $this;
	}
	public function SetWrapper ($wrapper) {
		$this->Wrapper = $wrapper;
		return $this;
	}
	public function SetUp () {
		parent::SetUp();
		$this->Form->AddJs($this->Js, $this->JsClass, array($this->Name));
		$this->Form->AddCss($this->Css);
	}
	public function RenderControl () {
		if ($this->Multiple) $this->Multiple = 'multiple';
		$attrsStr = $this->renderControlAttrsWithFieldVars(
			array('Min', 'Max', 'Step','Multiple')
		);
		$this->Multiple = $this->Multiple ? TRUE : FALSE ;
		$valueStr = $this->Multiple && gettype($this->Value) == 'array' ? implode(',', $this->Value) : (string)$this->Value;
		$result = SimpleForm_Core_View::Format(static::$templates->control, array(
			'id'		=> $this->Id, 
			'name'		=> $this->Name, 
			'type'		=> $this->Type,
			'value'		=> $valueStr . '" data-value="' . $valueStr,
			'attrs'		=> $attrsStr ? " $attrsStr" : '', 
		));
		return str_replace('{control}', $result, $this->Wrapper);
	}
}
