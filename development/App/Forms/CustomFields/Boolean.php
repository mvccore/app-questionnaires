<?php

namespace App\Forms\CustomFields;

use \MvcCore\Ext\Form;

class Boolean extends \MvcCore\Ext\Forms\Fields\RadioGroup
{
	protected $type = 'radio';

	protected $options = [
		'yes'	=> 'Yes',
		'no'	=> 'No',
	];

	protected $validators = [];

	public function __construct(array $cfg = []) {
		parent::__construct($cfg);
		/*$this->SetValidators([function($submitValue, $fieldName, $field, Form & $form) {
			$safeValue = strtolower(trim($submitValue));
			if (strlen($safeValue) > 0 && !in_array($safeValue, ['yes', 'no'])) {
				$safeValue = '';
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
		// if (!$this->translate) return $this; // boolean custom field - try to translate anyway 
		$form = & $this->form;
		foreach ($this->options as $key => & $value) 
			if ($value) 
				$this->options[$key] = $form->Translate($value);
		return $this;
	}
}
