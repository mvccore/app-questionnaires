<?php

class App_Forms_Fields_BooleanAndText extends SimpleForm_Core_FieldGroup
{
	public $Type = 'radio';
	public $Options = array(
		'yes'	=> 'Yes',
		'no'	=> 'No',
	);
	public $Value = array();
	public $Validators = array();
	protected static $templates = array(
		'control'			=> '<input id="{id}" name="{name}[]" type="{type}" value="{value}"{checked}{attrs} />',
	);
	public function __construct(array $cfg = array()) {
		parent::__construct($cfg);
		static::$templates = (object) array_merge((array)parent::$templates, (array)self::$templates);
		$this->SetValidators(array(function($submitValues, $fieldName, $field, SimpleForm & $form) {
			// possible values - empty array, array with one or two elements,
			//   first element could be boolean and also a text field
			$valid = TRUE;
			$safeValue = array();
			if ($field->Required && count($submitValues) < 2) $valid = FALSE;
			foreach ($submitValues as $key => $submitValue) {
				if ($key > 1) break;
				if ($key == 0 && count($submitValues) > 1) {
					$safeValueLocal = strtolower(trim($submitValue));
					$safeValueLocal = in_array($safeValueLocal, array('yes', 'no')) ? $safeValueLocal : '';
					if ($field->Required && !$safeValueLocal) $valid = FALSE;
					if ($safeValueLocal) $safeValue[] = $safeValueLocal;
				} else {
					$safeValueLocal = preg_replace("#[\\'\"\#`\<\>\[\]]#", '', trim($submitValue));
					$safeValue[] = mb_substr($safeValueLocal, 0, 255);
				}
			}
			if (!$valid) {
				$errorMsg = SimpleForm::$DefaultMessages[SimpleForm::VALID];
				if ($form->Translate) {
					$translator = $form->Translator;
					$errorMsg = $translator($errorMsg);
					$label = $field->Label ? $translator($field->Label) : $fieldName;
				} else {
					$label = $field->Label ? $field->Label : $fieldName;
				}
				$errorMsg = SimpleForm_Core_View::Format(
					$errorMsg, array($label)
				);
				$form->AddError(
					$fieldName, $errorMsg
				);
			}
			return $safeValue;
		}));
	}
	public function SetUp () {
		parent::SetUp();
		// if (!$this->Translate) return; // boolean custom field - try to translate anyway 
		$lang = $this->Form->Lang;
		$translator = $this->Form->Translator;
		if (!$translator) return;
		foreach ($this->Options as $key => $value) {
			if ($value) $this->Options[$key] = $translator((string)$value, $lang);
		}
	}
	public function RenderControl () {
		$result = parent::RenderControl();
		$result .= $this->RenderControlItemText('text', '');
		return $result;
	}
	public function RenderControlItemText ($key, $option) {
		$itemControlId = implode(SimpleForm::HTML_IDS_DELIMITER, array(
			$this->Form->Id, $this->Name, $key
		));
		$controlAttrsStr = $this->completeControlAttrsStr($key, $option);
		$value = count($this->Value) > 0 ? $this->Value[count($this->Value) - 1] : '';
		return SimpleForm_Core_View::Format(static::$templates->control, array(
			'id'		=> $itemControlId,
			'name'		=> $this->Name,
			'type'		=> 'text',
			'value'		=> $value,
			'checked'	=> '',
			'attrs'		=> $controlAttrsStr ? " $controlAttrsStr" : '',
		));
	}
	protected function completeControlAttrsStr ($key, $option) {
		$cssClassesBefore = array_merge($this->CssClasses, array());
		$labelAttrsBefore = array_merge($this->LabelAttrs, array());
		$controlAttrsBefore = array_merge($this->ControlAttrs, array());
		$requiredBefore = $this->Required;

		$this->CssClasses = array();
		$this->LabelAttrs = array();
		$this->ControlAttrs = array();
		foreach ($cssClassesBefore as $value) if ($value != 'error') $this->CssClasses[] = $value;
		foreach ($labelAttrsBefore as $key => $value) if ($key != 'required') $this->LabelAttrs[$key] = $value;
		foreach ($controlAttrsBefore as $key => $value) if ($key != 'required') $this->ControlAttrs[$key] = $value;
		$this->Required = FALSE;
		
		list(
			$itemLabelText_not_used, 
			$labelAttrsStr_not_used, 
			$controlAttrsStr
		) = $this->renderControlItemCompleteAttrsClassesAndText($key, $option);
		
		$this->CssClasses = $cssClassesBefore;
		$this->LabelAttrs = $labelAttrsBefore;
		$this->ControlAttrs = $controlAttrsBefore;
		$this->Required = $requiredBefore;

		return $controlAttrsStr;
	}
}