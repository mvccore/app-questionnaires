<?php

namespace App\Forms;

use \App\Forms\CustomFields,
	\MvcCore\Ext\Form,
	\MvcCore\Ext\Forms\Fields,
	\App\Models;

class Statistics extends Base
{
	protected $id = 'statistics-persons-filter';
	
	protected $cssClasses = 'statistics';

	protected $viewScript = 'statistics';
	
	protected $method = Form::METHOD_POST;

	/** @var bool */
	protected $authenticated = FALSE;
	/** @var bool */
	protected $personsForm = FALSE;
	/** @var int[] */
	protected $minMaxAges = [];
	/** @var \App\Models\Questionnaire */
	protected $questionnaire;
	
	public function GetAuthenticated () {
		return $this->authenticated;
	}
	public function & SetAuthenticated ($authenticated) {
		$this->authenticated = $authenticated;
		return $this;
	}
	
	public function GetPersonsForm () {
		return $this->personsForm;
	}
	public function & SetPersonsForm ($personsForm) {
		$this->personsForm = $personsForm;
		return $this;
	}

	public function & GetQuestionnaire () {
		return $this->questionnaire;
	}
	public function & SetQuestionnaire (Models\Questionnaire & $questionnaire = NULL) {
		$this->questionnaire = $questionnaire;
		return $this;
	}

	public function SetMinMaxAges ($minMaxAges = []) {
		$this->minMaxAges = $minMaxAges;
		return $this;
	}

    public function Init () {
		parent::Init();
		$this->initColumnsCount();
		if ($this->personsForm) {
			$age = new Fields\Range([
				'name'			=> 'age',
				'label'			=> 'Age',
				'min'			=> $this->minMaxAges[0],
				'max'			=> $this->minMaxAges[1],
				'step'			=> 1,
				'multiple'		=> TRUE,
				'cssClasses'	=> ['person', 'range', 'age'],
				'wrapper'		=> '{control}&nbsp;' . $this->Translate('years'),
			]);
			$sex = new Fields\CheckboxGroup([
				'name'			=> 'sex',
				'label'			=> 'Sex',
				'cssClasses'	=> ['person', 'radio-group', 'sex'],
				'options'		=> Models\Person::$SexOptions,
			]);
			$edu = new Fields\CheckboxGroup([
				'name'			=> 'education',
				'label'			=> 'Highest education level',
				'cssClasses'	=> ['person', 'radio-group', 'edu'],
				'options'		=> Models\Person::$EducationOptions,
				'viewScript'	=> 'field-group-with-columns',
				'columns'		=> $this->formColumnsCount,
			]);
			$job = new Fields\CheckboxGroup([
				'name'			=> 'job',
				'label'			=> 'I am',
				'cssClasses'	=> ['person', 'radio-group', 'job'],
				'options'		=> Models\Person::$JobOptions,
				'viewScript'	=> 'field-group-with-columns',
				'columns'		=> $this->formColumnsCount,
			]);
			$this->AddFields($age, $sex, $edu, $job);
		}
		if ($this->authenticated) {
			$from = new Fields\DateTime([
				'name'			=> 'from',
				'label'			=> 'From',
				'cssClasses'	=> ['person', 'from'],
			]);
			$to = new Fields\DateTime([
				'name'			=> 'to',
				'label'			=> 'To',
				'cssClasses'	=> ['person', 'to'],
			]);
			$this->AddFields($from, $to);
		}
		$submit = new Fields\SubmitButton([
			'name'			=> 'refresh',
			'value'			=> 'Refresh results',
			'cssClasses'	=> ['button', 'button-green'],
		]);
		$this->AddFields($submit);
		return $this;
	}
}
