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

	protected $validators = [];

	protected static $templates = [
		'control' => '<input id="{id}" name="{name}[]" type="{type}" value="{value}"{checked}{attrs} />',
	];

	public function __construct(array $cfg = []) {
		parent::__construct($cfg);
		static::$templates = (object) array_merge(
			(array) parent::$templates, 
			(array) self::$templates
		);
		/*$this->SetValidators([function($submitValues, $fieldName, $field, Form & $form) {
			// possible values - empty array, array with one or two elements,
			//   first element could be boolean and also a text field
			$valid = TRUE;
			$safeValue = [];
			if ($field->Required && count($submitValues) < 2) $valid = FALSE;
			foreach ($submitValues as $key => $submitValue) {
				if ($key > 1) break;
				if ($key == 0 && count($submitValues) > 1) {
					$safeValueLocal = strtolower(trim($submitValue));
					$safeValueLocal = in_array($safeValueLocal, ['yes', 'no']) ? $safeValueLocal : '';
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
					$errorMsg, [$label]
				);
				$form->AddError(
					$errorMsg, $fieldName
				);
			}
			return $safeValue;
		}]);*/
	}

	public function PreDispatch () {
		parent::PreDispatch();
		// if (!$this->Translate) return $this; // boolean custom field - try to translate anyway 
		$form = & $this->form;
		foreach ($this->options as $key => $value) 
			if ($value) 
				$this->options[$key] = $form->Translate($value);
		return $this;
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
		$value = count($this->value) > 0 
			? $this->value[count($this->value) - 1] 
			: '';
		$viewClass = $this->form->GetViewClass();
		return $viewClass::Format(static::$templates->control, [
			'id'		=> $itemControlId,
			'name'		=> $this->name,
			'type'		=> 'text',
			'value'		=> $value,
			'checked'	=> '',
			'attrs'		=> $controlAttrsStr ? " $controlAttrsStr" : '',
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
