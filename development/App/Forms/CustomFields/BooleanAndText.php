<?php

namespace App\Forms\CustomFields;

use \MvcCore\Ext\Form;

class BooleanAndText extends \MvcCore\Ext\Forms\FieldsGroup
{
	protected $type = 'radio';

	protected $options = [
		'yes'	=> 'Yes',
		'no'	=> 'No',
	];

	protected $value = [];

	protected $validators = ['BooleanAndText'];

	protected static $templates = [
		'control' => '<input id="{id}" name="{name}[]" type="{type}" value="{value}"{checked}{attrs} />',
	];

	public function __construct(array $cfg = []) {
		parent::__construct($cfg);
		static::$templates = (object) array_merge(
			(array) parent::$templates, 
			(array) self::$templates
		);
	}

	public function PreDispatch () {
		parent::PreDispatch();
		// if (!$this->Translate) return $this; // boolean custom field - try to translate anyway 
		$form = & $this->form;
		foreach ($this->options as $key => $value) 
			if ($value) 
				$this->options[$key] = $form->Translate($value);
	}

	public function RenderControl () {
		$result = parent::RenderControl();
		$result .= $this->RenderControlItemText('text', '');
		return $result;
	}

	public function RenderControlItemText ($key, $option) {
		$itemControlId = implode(Form::HTML_IDS_DELIMITER, [
			$this->form->GetId(), $this->name, $key
		]);
		$controlAttrsStr = $this->completeControlAttrsStr($key, $option);
		if (!$this->form->GetFormTagRenderingStatus()) 
			$controlAttrsStr .= (strlen($controlAttrsStr) > 0 ? ' ' : '')
				. 'form="' . $this->form->GetId() . '"';
		$value = count($this->value) > 0 
			? $this->value[count($this->value) - 1] 
			: '';
		$formViewClass = $this->form->GetViewClass();
		return $formViewClass::Format(static::$templates->control, [
			'id'		=> $itemControlId,
			'name'		=> $this->name,
			'type'		=> 'text',
			'value'		=> $value,
			'checked'	=> '',
			'attrs'		=> strlen($controlAttrsStr) > 0 ? ' ' . $controlAttrsStr : '',
		]);
	}

	protected function completeControlAttrsStr ($key, $option) {
		$cssClassesBefore = array_merge($this->cssClasses, []);
		$labelAttrsBefore = array_merge($this->labelAttrs, []);
		$controlAttrsBefore = array_merge($this->controlAttrs, []);
		$requiredBefore = $this->required;

		$this->cssClasses = [];
		$this->labelAttrs = [];
		$this->controlAttrs = [];
		foreach ($cssClassesBefore as $value) if ($value != 'error') $this->cssClasses[] = $value;
		foreach ($labelAttrsBefore as $key => $value) if ($key != 'required') $this->labelAttrs[$key] = $value;
		foreach ($controlAttrsBefore as $key => $value) if ($key != 'required') $this->controlAttrs[$key] = $value;
		$this->required = FALSE;
		
		list(,,$controlAttrsStr) = $this->renderControlItemCompleteAttrsClassesAndText($key, $option);
		
		$this->cssClasses = $cssClassesBefore;
		$this->labelAttrs = $labelAttrsBefore;
		$this->controlAttrs = $controlAttrsBefore;
		$this->required = $requiredBefore;

		return $controlAttrsStr;
	}
}
