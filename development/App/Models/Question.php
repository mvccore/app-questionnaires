<?php

class App_Models_Question extends App_Models_XmlModel
{
	/**
	* Public vars bellow is not necessary to define here manualy,
	* variables are initialized automaticly by xml file loading,
	* but here they are for IDE whispering
	*/
	public $Id;
	public $Text;
	public $Type;
	public $Checkboxes;
	public $Radios;
	public $Options;
	public $Connections;
	public $Required;
	public $Min;
	public $Max;
	public $MaxLength;
	public $Body;
	public $Delimiter;
	public $Solution;
	public $LevenstheinComparationTolerance;

	protected $xml;
	private static $_manualyParsedNodes = array(
		'radios'		=> 1, 
		'checkboxes'	=> 1,
		'options'		=> 1,
		'connections'	=> 1,
	);
	private static $_integerTypeNodes = array(
		'id'			=> 1,
		'min'			=> 1,
		'max'			=> 1,
		'max-length'	=> 1,
	);
	private static $_booleanTypeNodes = array(
		'required'		=> 1,
	);
    public function __construct() {
        //parent::__construct(); // not necessary for xml model
    }
	public static function GetByPath ($path = '') {
		if (is_null(static::$dataDir)) static::$dataDir = App_Models_Questionnaire::GetDataPath();
		return parent::GetByPath($path);
    }
	protected function xmlSetUp ($xml)
	{
		parent::xmlSetUp($xml);
		$methodName = '_xmlSetUp' . MvcCore_Tool::GetPascalCaseFromDashed($this->Type);
		if (method_exists($this, $methodName)) $this->$methodName();
	}
	private function _xmlSetUpRadios () {
		$nodes = $this->xmlGetNodes('radios/radio');
		$radios = array();
		foreach ($nodes as $node) {
			$radios[] = (string) $node;
		}
		$this->Radios = $radios;
	}
	private function _xmlSetUpCheckboxes () {
		$nodes = $this->xmlGetNodes('checkboxes/checkbox');
		$checks = array();
		foreach ($nodes as $node) {
			$checks[] = (string) $node;
		}
		$this->Checkboxes = $checks;
		if (isset($this->Solution)) {
			$solution = array();
			$answers = explode(',', $this->Solution);
			foreach ($answers as $answer) {
				$solution[] = intval($answer);
			}
			$this->Solution = $solution;
		}
	}
	private function _xmlSetUpConnections () {
		$nodes = $this->xmlGetNodes('options/option');
		$options = array();
		foreach ($nodes as $node) {
			$options[] = (string) $node;
		}
		$this->Options = $options;
		$nodes = $this->xmlGetNodes('connections/connection');
		$connections = array();
		foreach ($nodes as $node) {
			$connections[] = (string) $node;
		}
		$this->Connections = $connections;
		if (isset($this->Solution)) {
			$solution = array();
			$answers = explode(',', $this->Solution);
			foreach ($answers as $answer) {
				$keyValue = explode(':', $answer);
				$solution[intval($keyValue[0])] = intval($keyValue[1]);
			}
			$this->Solution = $solution;
		}
	}
	/*
	// rest of types are initialized automaticly:
	private function _xmlSetUpInteger () {}
	private function _xmlSetUpText () {}
	private function _xmlSetUpTextarea () {}
	private function _xmlSetUpBoolean () {}
	private function _xmlSetUpBooleanAndText () {}
	*/
}