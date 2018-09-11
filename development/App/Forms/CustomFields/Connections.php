<?php

namespace App\Forms\CustomFields;

class Connections extends \MvcCore\Ext\Forms\FieldsGroup
{
	protected $type = 'connections';
	
	protected $connections = [];

	protected $viewScript = 'connections';

	protected $validators = ['Connections'];
	
	protected $jsClassName = 'MvcCoreForm.Connections';

	protected $jsSupportingFile = \MvcCore\Ext\Forms\IForm::FORM_ASSETS_DIR_REPLACEMENT . '/fields/connections.js';
	
	public function & SetConnections ($connections) {
		$this->connections = $connections;
		return $this;
	}

	public function & GetConnections () {
		return $this->connections;
	}

	public function PreDispatch () {
		parent::PreDispatch();
		$form = & $this->form;
		$jsConstructorParams = [$this->name . '[]', $this->required];
		$form->AddJsSupportFile($this->jsSupportingFile, $this->jsClassName, $jsConstructorParams);
		if ($this->translate) return;
		foreach ($this->connections as $key => & $value) {
			if (gettype($value) == 'string') {
				// most simple key/value array options configuration
				if ($value) 
					$this->connections[$key] = $form->Translate($value);
			} else if (gettype($value) == 'array') {
				// advanced configuration with key, text, css class, and any other attributes for single option tag
				$text = isset($value['text']) ? $value['text'] : $key;
				if ($text) 
					$this->connections[$key]['text'] = $form->Translate($text);
			}
		}
	}
}
