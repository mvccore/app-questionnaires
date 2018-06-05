<?php

namespace App\Forms\CustomFields;

use \MvcCore\Ext\Form;

class Connections extends \MvcCore\Ext\Forms\FieldsGroup
{
	protected $type = 'connections';
	
	protected $connections = [];

	protected $viewScript = 'connections';

	protected $validators = [];
	
	protected $jsClassName = 'MvcCoreForm.Connections';

	protected $jsSupportingFile = \MvcCore\Ext\Forms\IForm::FORM_ASSETS_DIR_REPLACEMENT . '/fields/connections.js';
	
	/* setters *******************************************************************************/
	public function SetConnections ($connections) {
		$this->connections = $connections;
		return $this;
	}
	/* core methods **************************************************************************/
	public function __construct(array $cfg = []) {
		parent::__construct($cfg);
		static::$templates = (object) array_merge(
			(array)	parent::$templates, 
			(array)	self::$templates
		);
		/*$this->SetValidators([function($submitValues, $fieldName, $field, Form & $form) {
			$valid = TRUE;
			// filter submitted values for duplicated connections
			$safeValue = [];
			$valueKeys = [];
			$keysToUnset = [];
			foreach ($submitValues as $key => $submitValue) {
				$submitValue = preg_replace("#[^0-9]#", '', trim($submitValue));
				$submitValueInt = intval($submitValue);
				if (strlen($submitValue) > 0 && !isset($valueKeys[$submitValueInt])) {
					$valueKeys[$submitValueInt] = $key;
				} else if (strlen($submitValue) > 0) {
					$keysToUnset[] = $submitValueInt;
				}
			}
			foreach ($keysToUnset as $keyToUnset) {
				unset($valueKeys[$keyToUnset]);
				$valid = FALSE;
			}
			foreach ($valueKeys as $submitValueInt => $key) {
				$safeValue[intval($key)] = $submitValueInt;
			}
			if (!$valid || ($field->Required && count($field->Options) !== count($safeValue))) {
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
		if (!$this->translate) return $this;
		$form = & $this->form;
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
		$jsConstructorParams = [$this->name . '[]', $this->required];
		$form->AddJsSupportFile($this->jsSupportingFile, $this->jsClassName, $jsConstructorParams);
		return $this;
	}
}
