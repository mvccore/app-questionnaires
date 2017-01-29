<?php

namespace App\Forms\Fields;

use \MvcCore\Ext\Form;

class BooleanAndText extends Form\Core\FieldGroup
{
	public $Type = 'radio';
	public $Options = array(
		'yes'	=> 'Yes',
		'no'	=> 'No',
	);
	public $Value = array();
	public $Validators = array();
	public static $Templates = array(
		'control'			=> '<input id="{id}" name="{name}[]" type="{type}" value="{value}"{checked}{attrs} />',
	);
	public function __construct(array $cfg = array()) {
		parent::__construct($cfg);
		static::$Templates = (object) array_merge((array)parent::$Templates, (array)self::$Templates);
		$this->SetValidators(array(function($submitValues, $fieldName, $field, Form & $form) {
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
				$errorMsg = Form::$DefaultMessages[Form::VALID];
				if ($form->Translate) {
					$errorMsg = call_user_func($form->Translator, $errorMsg);
					$label = $field->Label ? call_user_func($form->Translator, $field->Label) : $fieldName;
				} else {
					$label = $field->Label ? $field->Label : $fieldName;
				}
				$errorMsg = Form\Core\View::Format(
					$errorMsg, array($label)
				);
				$form->AddError(
					$errorMsg, $fieldName
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
			if ($value) $this->Options[$key] = call_user_func($translator, (string)$value, $lang);
		}
	}
	public function RenderControl () {
		$result = parent::RenderControl();
		$result .= $this->RenderControlItemText('text', '');
		return $result;
	}
	public function RenderControlItemText ($key, $option) {
		$itemControlId = implode(Form::HTML_IDS_DELIMITER, array(
			$this->Form->Id, $this->Name, $key
		));
		$controlAttrsStr = $this->completeControlAttrsStr($key, $option);
		$value = count($this->Value) > 0 ? $this->Value[count($this->Value) - 1] : '';
		return Form\Core\View::Format(static::$Templates->control, array(
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