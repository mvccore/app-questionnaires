<?php

namespace App\Forms\Fields;

use \MvcCore\Ext\Form;

class Connections extends Form\Core\FieldGroup
{
	public $Type = 'connections';
	public $Options = array();
	public $Connections = array();
	public $TemplatePath = 'fields/connections';
	public $Validators = array();
	public $JsClass = 'MvcCoreForm.Connections';
	public $Js = '__MVCCORE_FORM_DIR__/fields/connections.js';
	/* setters *******************************************************************************/
	public function SetConnections ($connections) {
		$this->Connections = $connections;
		return $this;
	}
	/* core methods **************************************************************************/
	public function __construct(array $cfg = array()) {
		parent::__construct($cfg);
		static::$Templates = (object) array_merge((array)parent::$Templates, (array)self::$Templates);
		$this->SetValidators(array(function($submitValues, $fieldName, $field, Form & $form) {
			$valid = TRUE;
			// filter submitted values for duplicated connections
			$safeValue = array();
			$valueKeys = array();
			$keysToUnset = array();
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
		if (!$this->Translate) return;
		$lang = $this->Form->Lang;
		$translator = $this->Form->Translator;
		foreach ($this->Connections as $key => $value) {
			if (gettype($value) == 'string') {
				// most simple key/value array options configuration
				if ($value) $this->Connections[$key] = call_user_func($translator, (string)$value, $lang);
			} else if (gettype($value) == 'array') {
				// advanced configuration with key, text, css class, and any other attributes for single option tag
				$optObj = (object) $value;
				$text = isset($optObj->text) ? $optObj->text : $key;
				if ($text) {
					$this->Connections[$key]['text'] = call_user_func($translator, (string)$text, $lang);
				}
			}
		}
		$params = array($this->Name . '[]', $this->Required);
		$this->Form->AddJs($this->Js, $this->JsClass, $params);
	}
}