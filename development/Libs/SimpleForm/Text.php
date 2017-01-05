<?php

require_once('/Core/Field.php');
require_once('/Core/View.php');

class SimpleForm_Text extends SimpleForm_Core_Field
{
	public $Type = 'text';
	public $Placeholder = null;
	public $Size = null;
	public $Maxlength = null;
	public $Pattern = null;
	public $Autocomplete = null;
	public $Validators = array('SafeString', 'Maxlength', 'Pattern');
	public function SetPlaceholder ($placeholder) {
		$this->Placeholder = $placeholder;
		return $this;
	}
	public function SetSize ($size) {
		$this->Size = $size;
		return $this;
	}
	public function SetMaxlength ($maxlength) {
		$this->Maxlength = $maxlength;
		return $this;
	}
	public function SetPattern ($pattern) {
		$this->Pattern = $pattern;
		return $this;
	}
	public function SetAutocomplete ($autocomplete) {
		$this->Autocomplete = $autocomplete;
		return $this;
	}
	public function SetUp () {
		parent::SetUp();
		$form = $this->Form;
		$translator = $form->Translator;
		if ($this->Translate && $this->Placeholder) {
			$this->Placeholder = $translator($this->Placeholder, $form->Lang);
		}
	}
	public function RenderControl () {
		$attrsStr = $this->renderControlAttrsWithFieldVars(
			array('Maxlength', 'Size', 'Placeholder', 'Pattern', 'Autocomplete')
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
