<?php

namespace App\Forms\CustomFields;

use \MvcCore\Ext\Form;

class Boolean extends \MvcCore\Ext\Forms\Fields\RadioGroup
{
	protected $options = [
		'yes'	=> 'Yes',
		'no'	=> 'No',
	];

	public function PreDispatch () {
		parent::PreDispatch();
		// if (!$this->translate) return $this; // boolean custom field - try to translate anyway 
		$form = & $this->form;
		foreach ($this->options as $key => & $value) 
			if ($value) 
				$this->options[$key] = $form->Translate((string) $value);
	}
}
