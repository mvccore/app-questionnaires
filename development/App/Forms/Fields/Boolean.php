<?php

class App_Forms_Fields_Boolean extends SimpleForm_RadioGroup
{
	public $Type = 'radio';
	public $Options = array(
		'yes'	=> 'Yes',
		'no'	=> 'No',
	);
	public $Validators = array();
	public function __construct(array $cfg = array()) {
		parent::__construct($cfg);
		$this->SetValidators(array(function($submitValue, $fieldName, $field, SimpleForm & $form) {
			$safeValue = strtolower(trim($submitValue));
			if ($field->Required && !in_array($safeValue, array('yes', 'no'))) {
				$safeValue = '';
				$errorMsg = SimpleForm::$DefaultMessages[SimpleForm::VALID];
				if ($form->Translate) {
					$errorMsg = call_user_func($form->Translator, $errorMsg);
					$label = $field->Label ? call_user_func($form->Translator, $field->Label) : $fieldName;
				} else {
					$label = $field->Label ? $field->Label : $fieldName;
				}
				$errorMsg = SimpleForm_Core_View::Format(
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
}