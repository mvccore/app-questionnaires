<?php

namespace App\Forms\Fields;

use \MvcCore\Ext\Form;

class Boolean extends Form\RadioGroup
{
	public $Type = 'radio';
	public $Options = array(
		'yes'	=> 'Yes',
		'no'	=> 'No',
	);
	public $Validators = array();
	public function __construct(array $cfg = array()) {
		parent::__construct($cfg);
		$this->SetValidators(array(function($submitValue, $fieldName, $field, Form & $form) {
			$safeValue = strtolower(trim($submitValue));
			if (strlen($safeValue) > 0 && !in_array($safeValue, array('yes', 'no'))) {
				$safeValue = '';
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
}