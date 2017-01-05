<?php

require_once('/../../SimpleForm.php');
require_once('Field.php');

class SimpleForm_Core_Validator
{
	/**
	 * @var SimpleForm
	 */
	protected $Form = null;
	protected $Controller = null;
	protected $Translate = null;
	protected $Translator = null;
	protected static $validators = 'SafeString';
	protected static $validatorsClassNameTemplate = 'SimpleForm_Validators_{ValidatorName}';
	protected static $validatorsKeys = array();
	protected static $instances = array();
	public static function Create ($validatorName = '', SimpleForm & $form) {
		if (!self::$validatorsKeys) {
			$exploded = explode(',', self::$validators);
			foreach ($exploded as $value) self::$validatorsKeys[$value] = TRUE;
		}
		if (!isset(self::$validatorsKeys[$validatorName])) {
			if (strpos($validatorName, '_') !== FALSE) { // if not any full class name - go throw exception
				throw new Exception ("[SimpleForm_Core_Validator] Validator: '$validatorName' doesn't exist.");
			}
		}
		if (!isset(self::$instances[$validatorName])) {
			if (strpos($validatorName, '_') === FALSE) { // if not any full class name - it's built in validator
				$className = str_replace('{ValidatorName}', $validatorName, self::$validatorsClassNameTemplate);
			} else {
				$className = $validatorName;
			}
			self::$instances[$validatorName] = new $className($form);
		}
		return self::$instances[$validatorName];
	}
	public function __construct (SimpleForm & $form) {
		$this->Form = $form;
		$this->Controller = & $form->Controller;
		$this->Translate = $form->Translate;
		$this->Translator = $form->Translator;
	}
	public function Validate ($submitValue, $fieldName, SimpleForm_Core_Field & $field) {
		return TRUE;
	}
}
